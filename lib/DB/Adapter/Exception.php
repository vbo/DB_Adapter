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
 * @version 0.1 beta
 */

class DB_Adapter_Exception extends Exception
{
    /**
     * Primary information about error context.
     * In query errors its contains query text, etc...
     * @var string
     */
    public $primary_info;

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
    private $dbo;

    public function __construct($code, $primary_info, $message, $dbo)
    {
        parent::__construct($message, $code);
        $this->primary_info = $primary_info;
        $this->message = $message;
        $this->code = $code;
        $this->dbo = $dbo;
    }

    public function __toString()
    {
        $context = "unknown";
        require_once 'DB/Adapter/ErrorTracker.php';
        $c = DB_Adapter_ErrorTracker::findCaller($this->getTrace(), true);

        if ($c) {
            $context = (isset($c['file']) ? $c['file'] : '?');
            $context .= ' on line ' . (isset($c['line']) ? $c['line'] : '?');
        }

        $errmsg = get_class($this) . ($context ? " in {$context}" : "");
        $errmsg .= "\n" . rtrim($this->message);
        $errmsg .= "\n" . "Error occurred in {$this->primary_info}";
        return $errmsg;
    }
}