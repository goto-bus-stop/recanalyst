<?php

namespace RecAnalyst;

/**
 * Miscellaneous utilities for working with RecAnalyst.
 */
class Utils
{
    /**
     * Format a game time as "HH:MM:SS".
     *
     * @param int  $time  Game time in milliseconds.
     * @param string  $format  sprintf-style format.
     *     Defaults to %02d:%02d:%02d, for HH:MM:SS.
     * @return string Formatted string, or "-" if the time is 0. (Zero usually
     *     means "did not occur" or "unknown" in recorded game timestamps.)
     */
    public static function formatGameTime($time, $format = '%02d:%02d:%02d')
    {
        if ($time == 0) {
            return '-';
        }
        $hour   =  (int)($time / 1000 / 3600);
        $minute = ((int)($time / 1000 / 60)) % 60;
        $second = ((int)($time / 1000)) % 60;
        return sprintf($format, $hour, $minute, $second);
    }
}
