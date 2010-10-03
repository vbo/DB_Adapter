<?php

require_once 'DB/Adapter/Generic/Blob.php';

/**
 * Postgresql BLOB object implementation
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
 *
 * @todo Implement it
 */
class DB_Adapter_Postgresql_Blob extends DB_Adapter_Generic_Blob
{
    public $blob;
    public $id;
    public $database;

    public function __construct($database, $id=null)
    {
        $this->database = $database;
        $this->database->transaction();
        $this->id = $id;
        $this->blob = null;
    }

    public function read($len)
    {
        if ($this->id === false) {
            return '';
        }
        if (!($e=$this->_firstUse())) {
            return $e;
        }

        $data = @pg_lo_read($this->blob, $len);
        if ($data === false) {
            return $this->_raiseError('read');
        }
        return $data;
    }

    public function write($data)
    {
        if (!($e=$this->_firstUse())) {
            return $e;
        }

        $ok = @pg_lo_write($this->blob, $data);
        if ($ok === false) {
            return $this->_raiseError('add data to');
        }
        
        return true;
    }

    public function close()
    {
        if (!($e=$this->_firstUse())) {
            return $e;
        }
        if ($this->blob) {
            $id = @pg_lo_close($this->blob);
            if ($id === false) {
                return $this->_raiseError('close');
            }
            $this->blob = null;
        } else {
            $id = null;
        }
        $this->database->commit();
        return $this->id ? $this->id : $id;
    }

    public function length()
    {
        if (!($e=$this->_firstUse())) {
            return $e;
        }

        @pg_lo_seek($this->blob, 0, PGSQL_SEEK_END);
        $len = @pg_lo_tell($this->blob);
        @pg_lo_seek($this->blob, 0, PGSQL_SEEK_SET);

        if (!$len) {
            return $this->_raiseError('get length of');
        }
        return $len;
    }

    private function _firstUse()
    {
        if (is_resource($this->blob)) return true;

        if ($this->id !== null) {
            $this->blob = @pg_lo_open($this->database->link, $this->id, 'rw');
            if ($this->blob === false) {
                return $this->_raiseError('open');
            }
        } else {
            $this->id = @pg_lo_create($this->database->link);
            $this->blob = @pg_lo_open($this->database->link, $this->id, 'w');
            if ($this->blob === false) {
                return $this->_raiseError('create');
            }
        }
        return true;
    }

    function _raiseError($query)
    {
        return;
        $hId = $this->id === null? "null" : ($this->id === false? "false" : $this->id);
        $query = "-- $query BLOB $hId";
        $this->database->_setDbError($query);
    }
}