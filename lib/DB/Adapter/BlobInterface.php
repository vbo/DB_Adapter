<?php

/**
 * Generic database BLOB object interface
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
interface DB_Adapter_BlobInterface
{
    /**
     * Returns following $length bytes from the blob.
     * @return string
     */
    public function read($len);

    /**
     * Appends data to blob.
     * @return string
     */
    public function write($data);

    /**
     * Returns length of the blob.
     * @return int
     */
    public function length();

    /**
     * Closes the blob. Return its ID. No other way to obtain this ID!
     * @return int $blobid
     */
    public function close();
}
