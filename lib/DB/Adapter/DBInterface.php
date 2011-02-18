<?php

/**
 * DB_Adapter database object interface
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
interface DB_Adapter_DBInterface
{
    /**
     * Returns last user query text (for debug porposes)
     * @return string
     */
    public function getLastQuery($inline=false);

    /**
     * Create new blob
     * @return DB_Adapter_Generic_Blob
     */
    public function blob($blob_id = null);

    /**
     * Create new transaction.
     * @return mixed
     */
    public function transaction($mode=null);

    /**
     * Commit the transaction.
     * @return mixed
     */
    public function commit();

    /**
     * Rollback the transaction.
     * @return mixed
     */
    public function rollback();

    /**
     * Execute query and return the result.
     * @param  string  $query Query text
     * @param  mixed   [$arg1, [$arg2, [$arg3]]] Placeholders values
     * @return hash[]  $result
     */
    public function select($query);

    /**
     * Execute query and return the result.
     * @param  &int   $total Total number of records
     * @param  string $query Query text
     * @param  mixed  [$arg1, [$arg2, [$arg3]]] Placeholders values
     * @return hash[] $result
     */
    public function selectPage(&$total, $query);

    /**
     * Return the first row of query result.
     * If no one row found, return array()! It is useful while debugging,
     * because PHP DOES NOT generates notice on $row['abc'] if $row === null
     * or $row === false (but, if $row is empty array, notice is generated).
     * @param  string $query Query text
     * @param  mixed  [$arg1, [$arg2, [$arg3]]] Placeholders values
     * @return hash $result
     */
    public function selectRow($query);

    /**
     * Return the first column of query result as array.
     * @param  string $query Query text
     * @param  mixed  [$arg1, [$arg2, [$arg3]]] Placeholders values
     * @return array  $result
     */
    public function selectCol($query);

    /**
     * Return the first cell of the first column of query result.
     * If no one row selected, return null.
     * @param  string $query Query text
     * @param  mixed  [$arg1, [$arg2, [$arg3]]] Placeholders values
     * @return scalar $result
     */
    public function selectCell($query);

    /**
     * Alias for select(). May be used for INSERT, UPDATE, etc... queries.
     * @param  string $query Query text
     * @param  mixed  [$arg1, [$arg2, [$arg3]]] Placeholders values
     * @return scalar $result
     */
    public function query($query);

    /**
     * Enclose the string into database quotes correctly escaping
     * special characters. If $isIdent is true, value quoted as identifier
     * (e.g.: `value` in MySQL, "value" in Firebird, [value] in MSSQL).
     * @param  string $s       String for escape
     * @param  bool   $isIdent Its identifier value?
     * @return string $escaped
     */
    public function escape($s, $isIdent=false);

    /**
     * Set query logger called before each query is executed.
     * Returns previous logger.
     * @param  DB_Adapter_LoggerI/null $logger New logger instance
     * @return DB_Adapter_LoggerI/null $logger Prev logger instance
     */
    public function setLogger(DB_Adapter_LoggerInterface $logger);

    /**
     * Set identifier prefix used for $_ placeholder.
     * @param  string $prefix New prefix
     * @return string $prefix Prev prefix
     */
    public function setIdentPrefix($prefix=null);

    /**
     * Returns various statistical information.
     * @return array
     */
    public function getStatistics();
}
