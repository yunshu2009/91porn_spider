<?php

class Logging
{
    public static function debug($str)
    {
        echo $str;

        file_put_contents('./spider'.date('Ymd').'.log', $str, FILE_APPEND);
    }

    public static function error($str)
    {
        echo "\033[0;32m"." $str "."\033[0m\n";

        file_put_contents('./spider'.date('Ymd').'.log', $str, FILE_APPEND);
    }

    public static function progress($str)
    {
        echo $str;
    }
}
