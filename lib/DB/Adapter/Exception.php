<?php

/**
 * Base exception class.
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
 * @version 10.10 beta
 */
class DB_Adapter_Exception extends Exception
{
    /**
     * Primary information about error context.
     * In query errors it contains query text, etc...
     * @var string
     */
    public $primaryInfo;

    /**
     * Human-readable error message
     * @var string
     */
    public $message;

    /**
     * Numeric error code (for logging)
     * @var numeric
     */
    public $code;
    
    /**
     * Database adapter instance
     * @var DB_Adapter_Generic_DB
     */
    private $_dbo;

    public function __construct($code, $primaryInfo, $message, $dbo)
    {
        parent::__construct($message, $code);
        $this->primaryInfo = $primaryInfo;
        $this->message = $message;
        $this->code = $code;
        $this->_dbo = $dbo;
    }

    /**
     * Here we create human-readable representation of an error and its context.
     * @return string
     */
    public function __toString()
    {
        $context = "unknown";
        require_once 'DB/Adapter/ErrorTracker.php';
        $trace = DB_Adapter_ErrorTracker::findCaller($this->getTrace());
        if ($trace) {
            $context = (isset($trace[0]['file']) ? $trace[0]['file'] : '?');
            $context .= (isset($trace[0]['line']) ? "({$trace[0]['line']})" : '?');
            $traceAsString = $this->_traceToString($trace);
        }

        $errmsg = get_class($this) . ($context ? " in {$context}" : "");
        $errmsg .= "\n" . rtrim($this->message);
        $errmsg .= "\n" . "Error occurred in {$this->primaryInfo}";
        if ($traceAsString) {
            $errmsg .= "\n" . "Stack trace:\n" . $traceAsString;
        }
        return $errmsg;
    }

    private function _traceToString($trace)
    {        
        $srep = '';
        $levels = 0;
        foreach ($trace as $level=>$frame) {
            $func = (isset($frame['class']) ? "{$frame['class']}::" : '') . $frame['function'];
            $srep .= "#{$level} {$frame['file']}({$frame['line']}): {$func}(...)\n";
            $levels++;
        }
        $srep .= "#{$levels} {main}";
        return $srep;
    }
}