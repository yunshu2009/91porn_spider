<?php

include(__DIR__.'/vendor/autoload.php');

spl_autoload_register(function($class) {
    include(__DIR__.'/'.$class.'.php');
});
