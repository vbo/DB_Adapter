<?php

require_once 'DB/Adapter/Generic/Blob.php';

/**
 * MySQL BLOB object implementation
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
 * @todo    Test it
 */
class DB_Adapter_MySQL_Blob extends DB_Adapter_Generic_Blob
{
    // MySQL does not support separate BLOB fetching.
    private $blobdata = null;
    private $curSeek = 0;

    public function __construct($database, $blobdata=null)
    {
        $this->blobdata = $blobdata;
        $this->curSeek = 0;
    }

    public function read($len)
    {
        $p = $this->curSeek;
        $this->curSeek = min($this->curSeek + $len, strlen($this->blobdata));
        return substr($this->blobdata, $this->curSeek, $len);
    }

    public function write($data)
    {
        $this->blobdata .= $data;
    }

    public function close()
    {
        return $this->blobdata;
    }

    public function length()
    {
        return strlen($this->blobdata);
    }
}