<?php

require_once 'DB/Adapter/Generic/Blob.php';

/**
 * MySQL BLOB object implementation.
 * Blob behaviour (separate fetching) emulated, because of MySQL.
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
class DB_Adapter_MySQL_Blob extends DB_Adapter_Generic_Blob
{
    private $_blobdata = null;
    private $_curSeek = 0;

    public function __construct($database, $blobdata=null)
    {
        $this->_blobdata = $blobdata;
        $this->_curSeek = 0;
    }

    public function read($len)
    {
        $p = $this->_curSeek;
        $this->_curSeek = min($this->_curSeek + $len, strlen($this->_blobdata));
        return substr($this->_blobdata, $this->_curSeek, $len);
    }

    public function write($data)
    {
        $this->_blobdata .= $data;
    }

    public function close()
    {
        return $this->_blobdata;
    }

    public function length()
    {
        return strlen($this->_blobdata);
    }
}
