<?php

/**
 * Error tracker determines error context for comfort debugging
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
class DB_Adapter_ErrorTracker
{
    private static $ignoresInTraceRe = 'DB_Adapter_.*::.* | call_user_func.*';

    /**
     * Return stacktrace. Correctly work with call_user_func*
     * (totally skip them correcting caller references).
     * If $returnCaller is true, return only first matched caller,
     * not all stacktrace.
     *
     * @version 2.03
     * @todo Inc readability
     */
    public static function findCaller($trace=null, $returnCaller=false)
    {
        $smart = array();
        $framesSeen = 0;
        $ignoresRe = self::$ignoresInTraceRe;
        $ignoresRe = "/^(?>{$ignoresRe})$/six";
        if (is_null($trace)) {
            $trace = debug_backtrace();
        }
        
        for ($i = 0, $n = count($trace); $i < $n; $i++) {
            $t = $trace[$i];
            if (!$t) {
                continue;
            }
            
            // Next frame
            $next = isset($trace[$i + 1]) ? $trace[$i + 1] : null;
            // Dummy frame before call_user_func* frames
            // Skip call_user_func on next iteration
            if (!isset($t['file'])) {
                $t['over_function'] = $trace[$i + 1]['function'];
                $t = $t + $trace[$i + 1];
                $trace[$i + 1] = null;
            }

            // Skip myself frame
            if (++$framesSeen < 2) {
                continue;
            }
            // Skip frames for functions
            // situated in ignored places
            if ($next) {
                $frameCaller = '';
                if (isset($next['class'])) {
                    $frameCaller .= $next['class'] . '::';
                }
                if (isset($next['function'])) {
                    $frameCaller .= $next['function'];
                }
                if (preg_match($ignoresRe, $frameCaller)) {
                    continue;
                }
            }

            // On each iteration we consider ability to add PREVIOUS frame
            // to $smart stack.
            if ($returnCaller) {
                return $t;
            }
            $smart[] = $t;
        }

        return $smart;
    }
}
