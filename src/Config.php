<?php

class Config
{
    static $proxyIp = 'socks5://127.0.0.1:1086';
    static $proxy = false;

    static $host = '627.workarea7.live';

    static $allLists = [
        'category=top&viewtype=basic' => [2, 4],     //本月最热
//        'category=mf&viewtype=basic' => 5,       //收藏最多
    ];

    static $memoryLimit = '512M';

    static $path = __DIR__.'/video';
}
