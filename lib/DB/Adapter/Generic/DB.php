<?php

/**
 * Use this constant as placeholder value to skip optional SQL block {...}.
 */
define('DB_ADAPTER_SKIP', log(0));

/**
 * Generic database adapter class
 *
 * @package DB_Adapter
 *
 * DB_Adapter PHP library provides elegant interface for some SQL databases.
 * It supports several types of handy and secure placeholders
 * and provide comfortable debugging.
 *
 * (c) DB_Adapter community
 * @see http://db-adapter.in-source.ru
 * 
 * Original idea by Dmitry Koterov and Konstantin Zhinko
 * @see http://dklab.ru/lib/DbSimple/
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 * @see http://www.gnu.org/copyleft/lesser.html
 *
 * @author  Borodin Vadim <vb@in-source.ru>
 * @version 0.1 beta
 */
abstract class DB_Adapter_Generic_DB
{
    /**
     * Names of special columns in result-set which is used
     * as array key (or karent key in forest-based resultsets) in
     * resulting hash.
     */
    const ARRAY_KEY_COL  = 'ARRAY_KEY';
    const PARENT_KEY_COL = 'PARENT_KEY';

    /**
     * When string representation of row (in characters) is greater than this,
     * row data will not be logged.
     */
    const MAX_LOG_ROW_LEN = 128;

    /**
     * Identifiers prefix (used for ?_ placeholder).
     * @var string
     */
    private $_identPrefix = '';

    /**
     * Queries statistics
     * @var array
     */
    private $_statistics = array(
        'time'  => 0,
        'count' => 0,
    );

    /**
     * Holdes last user query
     * @var array/string
     */
    protected $_lastQuery;

    /**
     * Logger instance
     * @var DB_Adapter_LoggerI
     */
    private $_logger;
    
    private $_placeholderArgs, $_placeholderNativeArgs, $_placeholderCache=array();
    private $_placeholderNoValueFound;

    /**
     * Returns last user query text (for debug porposes)
     * @return string
     */
    public function getLastQuery ()
    {
        $q = $this->_lastQuery;
        if (is_array($q)) {
            $this->_expandPlaceholders($q);
            $q = $q[0];
        }

        return $q;
    }

    /**
     * Create new blob
     * @return DB_Adapter_Generic_Blob
     */
    public function blob ($blob_id = null)
    {
        return $this->_performNewBlob($blob_id);
    }

    /**
     * Create new transaction.
     * @return mixed
     */
    public function transaction ($mode=null)
    {
        $this->_logQuery('-- START TRANSACTION ' . $mode);
        return $this->_performTransaction($mode);
    }

    /**
     * Commit the transaction.
     * @return mixed
     */
    public function commit ()
    {
        $this->_logQuery('-- COMMIT');
        return $this->_performCommit();
    }

    /**
     * Rollback the transaction.
     * @return mixed
     */
    public function rollback ()
    {
        $this->_logQuery('-- ROLLBACK');
        return $this->_performRollback();
    }

    /**
     * Execute query and return the result.
     * @param  string  $query Query text
     * @param  mixed   [$arg1, [$arg2, [$arg3]]] Placeholders values
     * @return hash[]  $result
     */
    public function select ($query)
    {
        $total = false;
        $args  = func_get_args();
        return $this->_query($args, $total);
    }

    /**
     * Execute query and return the result.
     * @param  &int   $total Total number of records
     * @param  string $query Query text
     * @param  mixed  [$arg1, [$arg2, [$arg3]]] Placeholders values
     * @return hash[] $result
     */
    public function selectPage (&$total, $query)
    {
        $total = true;
        $args  = func_get_args();
        array_shift($args);
        return $this->_query($args, $total);
    }

    /**
     * Return the first row of query result.
     * If no one row found, return array()! It is useful while debugging,
     * because PHP DOES NOT generates notice on $row['abc'] if $row === null
     * or $row === false (but, if $row is empty array, notice is generated).
     * @param  string $query Query text
     * @param  mixed  [$arg1, [$arg2, [$arg3]]] Placeholders values
     * @return hash $result
     */
    public function selectRow ($query)
    {
        $total = false;
        $args  = func_get_args();
        $rows  = $this->_query($args, $total);

        if (!is_array($rows)) return $rows;
        if (!count($rows))    return array();
        reset($rows);
        return current($rows);
    }

    /**
     * Return the first column of query result as array.
     * @param  string $query Query text
     * @param  mixed  [$arg1, [$arg2, [$arg3]]] Placeholders values
     * @return array  $result
     */
    public function selectCol ($query)
    {
        $total = false;
        $args  = func_get_args();
        $rows  = $this->_query($args, $total);
        if (!is_array($rows)) return $rows;
        $this->_shrinkLastArrayDimensionCallback($rows);        
        return $rows;
    }

    /**
     * Return the first cell of the first column of query result.
     * If no one row selected, return null.
     * @param  string $query Query text
     * @param  mixed  [$arg1, [$arg2, [$arg3]]] Placeholders values
     * @return scalar $result
     */
    public function selectCell ($query)
    {
        $total = false;
        $args  = func_get_args();
        $rows  = $this->_query($args, $total);

        if (!is_array($rows)) return $rows;
        if (!count($rows)) return null;
        reset($rows);
        $row = current($rows);
        if (!is_array($row)) return $row;        
        reset($row);

        return current($row);
    }

    /**
     * Alias for select(). May be used for INSERT, UPDATE, etc... queries.
     * @param  string $query Query text
     * @param  mixed  [$arg1, [$arg2, [$arg3]]] Placeholders values
     * @return scalar $result
     */
    public function query ($query)
    {
        $total = false;
        $args  = func_get_args();        
        return $this->_query($args, $total);
    }

    /**
     * Enclose the string into database quotes correctly escaping
     * special characters. If $isIdent is true, value quoted as identifier
     * (e.g.: `value` in MySQL, "value" in Firebird, [value] in MSSQL).
     * @param  string $s       String for escape
     * @param  bool   $isIdent Its identifier value?
     * @return string $escaped
     */
    public function escape ($s, $isIdent=false)
    {
        return $this->_performEscape($s, $isIdent);
    }

    /**
     * Set query logger called before each query is executed.
     * Returns previous logger.
     * @param  DB_Adapter_LoggerI/null $logger New logger instance
     * @return DB_Adapter_LoggerI/null $logger Prev logger instance
     */
    public function setLogger ($logger)
    {
        $prev = $this->_logger;
        $this->_logger = $logger;        
        return $prev;
    }

    /**
     * Set identifier prefix used for $_ placeholder.
     * @param  string $prx New prefix
     * @return string $prx Prev prefix
     */
    public function setIdentPrefix ($prx=null)
    {
        $old = $this->_identPrefix;
        if (!is_null($prx)) $this->_identPrefix = $prx;
        return $old;
    }

    /**
     * Returns various statistical information.
     * @return array
     */
    public function getStatistics ()
    {
        return $this->_statistics;
    }

    /**
     * @return string
     */
    protected abstract function _performEscape ($s, $isIdent=false);

    /**
     * @return DB_Adapter_Generic_Blob $blob
     */
    protected abstract function _performNewBlob ($blobid=null);

    /**
     * @return array $fields List of BLOB fields names in result set
     */
    protected abstract function _performGetBlobFieldNames ($result);

    /**
     * Transform query different way specified by $how.
     * May return some information about performed transform.
     * @param array& $queryMain
     * @param string $how
     */
    protected abstract function _performTransformQuery (array& $queryMain, $how);

    /**
     * Must return:
     * - For SELECT queries: ID of result-set (PHP resource).
     * - For other  queries: query status (scalar).
     * - For error  queries: null.
     * @return mixed $result
     */
    protected abstract function _performQuery (array $queryMain);

    /**
     * Fetch ONE NEXT row from result-set.
     * Must return:
     * - For SELECT queries: all the rows of the query (2d array).
     * - For INSERT queries: ID of inserted row.
     * - For UPDATE queries: number of updated rows.
     * - For other  queries: query status (scalar).
     * - For error  queries: throw an Exception.
     * @return mixed $result
     */
    protected abstract function _performFetch ($result);

    /**
     * Start new transaction.
     * @return mixed $result
     */
    protected abstract function _performTransaction ($mode=null);

    /**
     * Commit the transaction.
     * @return mixed $result
     */
    protected abstract function _performCommit ();

    /**
     * Rollback the transaction.
     * @return mixed $result
     */
    protected abstract function _performRollback ();

    /**
     * Return regular expression which matches ignored query parts.
     * This is needed to skip placeholder replacement inside comments, constants etc.
     * default ''
     * @return string
     */
    protected function _performGetPlaceholderIgnoreRe ()
    {
        return '';
    }

    /**
     * Returns marker for native database placeholder. E.g. in FireBird it is '?',
     * in PostgreSQL - '$1', '$2' etc.     *
     * @param  int $n Number of native placeholder from the beginning of the query (begins from 0!).
     * @return string String representation of native placeholder marker (by default - '?').
     */
    protected function _performGetNativePlaceholderMarker ($n)
    {
        return '?';
    }

    /**
     * @see _performQuery().
     * @return array
     */
    private function _query (array $query, &$total)
    {
        $this->attributes = $this->_transformQuery($query, 'GET_ATTRIBUTES');
        if ($total) $this->_transformQuery($query, 'CALC_TOTAL');

        $this->_logQuery($query);
        $qStart = $this->_microtime();
        $result = $this->_performQuery($query);
        $fetchTime = $firstFetchTime = 0;

        if (is_resource($result)) {
            $rows = array();
            $fStart = $this->_microtime();
            $row    = $this->_performFetch($result);
            $firstFetchTime = $this->_microtime() - $fStart;

            if (!is_null($row)) {
                $rows[] = $row;
                while ($row=$this->_performFetch($result)) {
                    $rows[] = $row;
                }
            }

            $fetchTime = $this->_microtime() - $fStart;
        } else {
            $rows = $result;
        }

        $queryTime = $this->_microtime() - $qStart;
        $this->_logQueryStat($queryTime, $fetchTime, $firstFetchTime, $rows);

        $blobs_exist = is_array($rows) && !empty($this->attributes['BLOB_OBJ']);
        if ($blobs_exist) {
            $blobFieldNames = $this->_performGetBlobFieldNames($result);
            foreach ($blobFieldNames as $name) {
                for ($r = count($rows)-1; $r>=0; $r--) {
                    $rows[$r][$name] =& $this->_performNewBlob($rows[$r][$name]);
                }
            }
        }

        $result = $this->_transformResult($rows);
        if (is_array($result) && $total) {
            $this->_transformQuery($query, 'GET_TOTAL');            
            $total = $this->selectCell($query);
        }

        return $result;
    }

    /**
     * Transform query different way specified by $how.
     * May return some information about performed transform.
     * @return mixed
     * @todo Do it without switch stmt?
     */
    private function _transformQuery (array& $query, $how)
    {
        // Do overriden transformation.
        $result = $this->_performTransformQuery($query, $how);
        if ($result === true) return $result;

        // Do common transformations.
        switch ($how) {
            case 'GET_ATTRIBUTES':
            {
                $options = array();
                $q = $query[0];
                $m = null;

                while (preg_match('/^ \s* -- [ \t]+ (\w+): ([^\r\n]+) [\r\n]* /sx', $q, $m)) {
                    $options[$m[1]] = trim($m[2]);
                    $q = substr($q, strlen($m[0]));
                }

                return $options;
                break;
            }
        }
    }

    /**
     * Replace placeholders by quoted values.
     * Modify $queryAndArgs.
     * @return void
     */
    protected function _expandPlaceholders (&$queryAndArgs, $useNative=false)
    {
        $cacheCode = null;
        // @todo Determine, why Dmitry use PH cache only with logging
        if ($this->_logger) {
            // Serialize is much faster than placeholder expansion. So use caching.
            $cacheCode = md5(serialize($queryAndArgs) . '|' . $useNative . '|' . $this->_identPrefix);
            if (isset($this->_placeholderCache[$cacheCode])) {
                $queryAndArgs = $this->_placeholderCache[$cacheCode];
                return;
            }
        }

        if (!is_array($queryAndArgs)) $queryAndArgs = array($queryAndArgs);
        $this->_placeholderNativeArgs = $useNative ? array() : null;
        $this->_placeholderArgs       = array_reverse($queryAndArgs);

        // array_pop is faster than array_shift
        $query = array_pop($this->_placeholderArgs); 
        // Do all the work.
        $this->_placeholderNoValueFound = false;
        $query = $this->_expandPlaceholdersFlow($query);

        if ($useNative) {
            array_unshift($this->_placeholderNativeArgs, $query);
            $queryAndArgs = $this->_placeholderNativeArgs;
        } else {
            $queryAndArgs = array($query);
        }

        if ($cacheCode) $this->_placeholderCache[$cacheCode] = $queryAndArgs;
    }


    /**
     * Do real placeholder processing.
     * Imply that all interval variables (_placeholder_*) already prepared.
     * May be called recurrent!
     */
    private function _expandPlaceholdersFlow ($query)
    {
        $re = '{
            (?>
                # Ignored chunks.
                (?>
                    # Comment.
                    -- [^\r\n]*
                )
                  |
                (?>
                    # DB-specifics.
                    ' . trim($this->_performGetPlaceholderIgnoreRe()) . '
                )
            )
              |
            (?>
                # Optional blocks
                \{
                    # Use "+" here, not "*"! Else nested blocks are not processed well.
                    ( (?> (?>[^{}]+)  |  (?R) )* )             #1
                \}
            )
              |
            (?>
                # Placeholder
                (\?) ( [_dsafn\#]? )                           #2 #3
            )
        }sx';

        $query = preg_replace_callback(
            $re,
            array($this, '_expandPlaceholdersCallback'),
            $query
        );
        
        return $query;
    }


    /**
     * string _expandPlaceholdersCallback(list $m)
     * Internal function to replace placeholders (see preg_replace_callback).
     */
    private function _expandPlaceholdersCallback ($m)
    {
        // Placeholder.
        if (!empty($m[2]))
        {
            $type = $m[3];
            // Idenifier prefix.
            if ($type == '_') return $this->_identPrefix;
            // Value-based placeholder.
            if (!$this->_placeholderArgs) return 'DB_ADAPTER_ERROR_NO_VALUE';
            $value = array_pop($this->_placeholderArgs);
            // Skip this value?
            if (DB_ADAPTER_SKIP === $value) {
                $this->_placeholderNoValueFound = true;
                return '';
            }

            // First process guaranteed non-native placeholders.
            switch ($type) {
                case 'a': // Array
                    if (!$value) $this->_placeholderNoValueFound = true;
                    if (!is_array($value)) return 'DB_ADAPTER_ERROR_VALUE_NOT_ARRAY';

                    $parts = array();
                    foreach ($value as $k=>$v) {
                        if     ($v === null)     $v = 'NULL';
                        elseif (is_string($v))   $v = $this->escape($v);
                        elseif (is_bool($v))     $v = (int) $v;
                        elseif (!is_numeric($v)) $v = 'DB_ADAPTER_ERROR_VALUE';                        
                        if (!is_int($k)) {
                            $k = $this->escape($k, $isIdent = true);
                            $parts[] = "$k=$v";
                        } else {
                            $parts[] = $v;
                        }
                    }
                    
                    return join(', ', $parts);
                    break;

                case "#": // Identifier
                    if (!is_array($value)) return $this->escape($value, $isIdent = true);
                    $parts = array();
                    foreach ($value as $table=>$identifier)
                    {
                        if (!is_string($identifier)) return 'DB_ADAPTER_ERROR_ARRAY_VALUE_NOT_STRING';
                        // Else we gonna construct simething like `field` or `tbl`.`field`
                        $parts[] = (!is_int($table) ? $this->escape($table, true) . '.' : '') . $this->escape($identifier, true);
                    }

                    return join(', ', $parts);
                    break;
                    
                case 'n': // Key
                    return empty($value) ? 'NULL' : intval($value);
                    break;
            }

            // Native arguments are not processed.
            if ($this->_placeholderNativeArgs !== null)
            {
                $this->_placeholderNativeArgs[] = $value;
                return $this->_performGetNativePlaceholderMarker(count($this->_placeholderNativeArgs) - 1);
            }

            // In non-native mode arguments are quoted.
            if ($value === null) return 'NULL';
            switch ($type)
            {
                case '':
                    if (!is_scalar($value)) return 'DB_ADAPTER_ERROR_VALUE_NOT_SCALAR';
                    else                    return $this->escape($value);
                    break;

                case 'd':
                    return intval($value);
                    break;

                case 'f':
                    return str_replace(',', '.', floatval($value));
                    break;
            }
            
            // By default - escape as string
            return $this->escape($value);
        }

        // Optional block
        if (isset($m[1]) && strlen($block=$m[1])) {
            $prev  = @$this->_placeholderNoValueFound;
            $block = $this->_expandPlaceholdersFlow($block);
            $block = $this->_placeholderNoValueFound ? '' : ' ' . $block . ' ';
            $this->_placeholderNoValueFound = $prev; // recurrent-safe
            
            return $block;
        }

        // Default: skipped part of the string.
        return $m[0];
    }

    /**
     * Return microtime as float value.
     */
    private static function _microtime()
    {
        $t = explode(" ", microtime());
        return $t[0] + $t[1];
    }

    /**
     * Convert SQL field-list to COUNT(...) clause
     * (e.g. 'DISTINCT a AS aa, b AS bb' -> 'COUNT(DISTINCT a, b)').
     */
    protected static function _fieldList2Count ($fields)
    {
        $m = null;
        
        if (preg_match('/^\s* DISTINCT \s* (.*)/sx', $fields, $m)) {
            $fields = $m[1];
            $fields = preg_replace('/\s+ AS \s+ .*? (?=,|$)/sx', '', $fields);
            return "COUNT(DISTINCT $fields)";
        }    
        
        return 'COUNT(*)';
    }

    /**
     * Transform resulting rows to various formats.
     * @param  array $rows
     * @return array $result
     */
    private static function _transformResult ($rows)
    {
        // Process ARRAY_KEY feature.
        if (is_array($rows) && $rows) {
            // Find ARRAY_KEY* AND PARENT_KEY fields in field list.
            $pk = null;
            $ak = array();
            foreach (current($rows) as $fieldName => $dummy) {
                if (0 == strncasecmp($fieldName, self::ARRAY_KEY_COL, strlen(self::ARRAY_KEY_COL))) {
                    $ak[] = $fieldName;
                } elseif (0 == strncasecmp($fieldName, self::PARENT_KEY_COL, strlen(self::PARENT_KEY_COL))) {
                    $pk = $fieldName;
                }
            }
            
            natsort($ak);
            if ($ak) {
                // Tree-based array? Fields: ARRAY_KEY, PARENT_KEY
                if ($pk !== null) return self::_transformResultToForest($rows, $ak[0], $pk);
                // Key-based array? Fields: ARRAY_KEY.
                return self::_transformResultToHash($rows, $ak);
            }
        }
        
        return $rows;
    }


    /**
     * Converts rowset to key-based array.
     * @param  array $rows   Two-dimensional array of resulting rows.
     * @param  array $ak     List of ARRAY_KEY* field names.
     * @return array         Transformed array.
     */
    private static function _transformResultToHash($rows, $arrayKeys)
    {
        $result = array();
        foreach ($rows as $row) {
            $current =& $result;
            // Iterate over all of ARRAY_KEY* fields and build array dimensions.
            foreach ($arrayKeys as $ak) {
                $key = $row[$ak];
                unset($row[$ak]); // remove ARRAY_KEY* field from result row
                if ($key !== null) {
                    $current =& $current[$key];
                } else {
                    // IF ARRAY_KEY field === null, use array auto-indices.
                    // we use $tmp, because don't know the value of auto-index
                    $tmp       =  array();
                    $current[] =& $tmp;
                    $current   =& $tmp;
                    unset($tmp); 
                }
            }

            $current = $row; // save the row in last dimension
        }

        return $result;
    }


    /**
     * Converts rowset to the forest.
     * @param array $rows       Two-dimensional array of resulting rows.
     * @param string $idName    Name of ID field.
     * @param string $pidName   Name of PARENT_ID field.
     * @return array            Transformed array (tree).
     */
    private static function _transformResultToForest($rows, $idName, $pidName)
    {
        $ids      = array();
        $children = array();
        // Collect who are children of whom.
        foreach ($rows as $i=>$r) {
            $row =& $rows[$i];
            $id  =  $row[$idName];
            $pid =  $row[$pidName];

            if ($id === null) continue;     // Bug of tree structure
            if ($id == $pid)  $pid = null;  // Strange tree implementation

            $children[$pid][$id] =& $row;
            if (!isset($children[$id])) {
                $children[$id] = array();
            }

            $ids[$id]          =  true;
            $row['childNodes'] =& $children[$id];            
        }

        // Root elements are elements with non-found PIDs.
        $forest = array();
        foreach ($rows as $i=>$r) {
            $row  =& $rows[$i];
            $id   =  $row[$idName];
            $pid  =  $row[$pidName];

            if ($pid == $id)        $pid = null;
            if (!isset($ids[$pid])) $forest[$row[$idName]] =& $row;

            unset($row[$idName]);
            unset($row[$pidName]);
        }
        
        return $forest;
    }

    /**
     * Replaces the last array in a multi-dimensional array $V by its first value.
     * Used for selectCol(), when we need to transform (N+1)d resulting array
     * to Nd array (column).
     */
    private static function _shrinkLastArrayDimensionCallback (&$v)
    {
        if (!$v) return;
        reset($v);

        if (!is_array($firstCell = current($v))) {
            $v = $firstCell;
        } else {
            array_walk(
                $v,
                array(__CLASS__, '_shrinkLastArrayDimensionCallback')
            );
        }
    }

    /**
     * Must be called on each query.
     * If $noTrace is true, library caller is not solved (speed improvement).
     * @todo Fix it
     * @return void
     */
    protected function _logQuery ($query, $noTrace=false)
    {
        if (!$this->_logger) return;

        $this->_expandPlaceholders($query, $useNative = false);
        $message = $query[0];
        $context = null;

        if (!$noTrace) {
            require_once 'DB/Adapter/ErrorTracker.php';
            $context = DB_Adapter_ErrorTracker::findCaller($trace=null, $returnCaller=true);
        }

        $this->_logger->log($context, $message);
    }

    /**
     * Log information about performed query statistics.
     */
    private function _logQueryStat ($queryTime, $fetchTime, $firstFetchTime, $rows)
    {
        // Always increment counters.
        $this->_statistics['time'] += $queryTime;
        $this->_statistics['count']++;

        // If no logger, economize CPU resources and actually log nothing.
        if (!$this->_logger) {
            return;
        }

        $dt             = round($queryTime      * 1000);
        $firstFetchTime = round($firstFetchTime * 1000);
        $tailFetchTime  = round($fetchTime      * 1000) - $firstFetchTime;

        $log = "  -- ";
        if ($firstFetchTime + $tailFetchTime) {
            $log = sprintf(
                "  -- %d ms = %d+%d". ($tailFetchTime? "+%d" : ""),
                $dt,
                $dt - $firstFetchTime - $tailFetchTime,
                $firstFetchTime,
                $tailFetchTime
            );
        } else {
            $log = sprintf("  -- %d ms", $dt);
        }

        $log .= "; returned ";
        if (!is_array($rows)) {
            $log .= $this->escape($rows);
        } else {
            $detailed = null;
            if (count($rows) == 1) {
                $len = 0; $values = array();
                foreach ($rows[0] as $k=>$v) {
                    $len += strlen($v);
                    if ($len > self::MAX_LOG_ROW_LEN) {
                        break;
                    }

                    $values[] = $v === null? 'NULL' : $this->escape($v);
                }

                if ($len <= self::MAX_LOG_ROW_LEN) {
                    $detailed = "(" . preg_replace("/\r?\n/", "\\n", join(', ', $values)) . ")";
                }
            }

            if ($detailed) {
                $log .= $detailed;
            } else {
                $log .= count($rows). " row(s)";
            }
        }

        $this->_logQuery($log, true);
    }   
};