<?php

namespace Riskyworks\WordpressMigrator\Helpers;

class DebugHelper
{
    public static function dd($v)
    {
        self::beginOutput();
        var_dump($v);
        self::endOutput();
    }

    public static function beginOutput() {
        echo "<pre>";
    }

    public static function endOutput() {
        echo "</pre>";
    }
}