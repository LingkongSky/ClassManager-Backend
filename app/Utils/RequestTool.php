<?php

namespace App\Utils;

class RequestTool
{
    public static function xxs($key)
    {
        $key = nl2br($key);
        $key = htmlspecialchars($key);
        $key = addslashes($key);
        $key = strip_tags($key);
        $key = preg_replace('/\/\/|#/', '', $key);
        $key = trim($key);
        return $key;
    }
}
