<?php

namespace Elao\ErrorNotifierBundle\Util;

class MemoryManagement
{
    private static $tmpBuffer = null;

    /**
     * This allows to catch memory limit fatal errors.
     */
    public static function reserveMemory()
    {
        self::$tmpBuffer = str_repeat('x', 1024 * 500);
    }

    public static function freeMemory()
    {
        self::$tmpBuffer = '';
    }
}