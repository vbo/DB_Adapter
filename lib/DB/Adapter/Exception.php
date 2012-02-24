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
 * @see http://db-adapter.vbo.name
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
 * @author  Borodin Vadim <vbo@vbo.name>
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
     * @var array
     */
    public $smartTrace;

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

        $this->_processTrace();
    }

    private function _processTrace()
    {
        require_once 'DB/Adapter/ErrorTracker.php';
        $this->smartTrace = DB_Adapter_ErrorTracker::findCaller($this->getTrace());
        $this->file = (isset($this->smartTrace[0]['file'])) ? $this->smartTrace[0]['file'] : $this->file;
        $this->line = (isset($this->smartTrace[0]['line'])) ? $this->smartTrace[0]['line'] : $this->line;
    }
}
