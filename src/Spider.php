<?php

include "autoload.php";

// 爬虫调度器
class Spider
{
    // 解析器
    private $parser;
    // 下载器
    private $downloader;

    public $name = '91porn-spider';
    private $startUrls;

    public function __construct()
    {
        $this->parser = new Parser();
        $this->downloader = new Downloader();

        foreach (Config::$allLists as $url => $num) {
            $url = 'http://'.Config::$host.'/v.php?'.$url;
            $this->startUrls[$url] = $num;
        }
    }

    public function crawl()
    {
        $currPage = 0;
        foreach ($this->startUrls as $pageUrl => $limitPage) {
            list($currPage, $pageNum) = $limitPage;
            while ($currPage <= $pageNum) {
                $url = sprintf("%s&page=%d", $pageUrl, $currPage);

                Logging::debug("get url:".$url."\n");
                Logging::debug('page:'.$currPage."...\n");

                $content = $this->downloader->getHtml($url);
                if (! $content) {
                    Logging::error("get data empty");
                    continue ;
                }

                $list = $this->parser->getList($content);
                foreach ($list as $item) {
                    $title = $item['title'];
                    $detailUrl = $item['detailUrl'];
                    Logging::debug('title:'.$title.', detail url:'.$detailUrl."\n");

                    list($videoUrl, $videoDate) = $this->crawlDetail($detailUrl);
                    Logging::debug('mp4 url:'.$videoUrl."\n");

                    if ($videoUrl) {
                        $this->downloader->downloadVideo($videoUrl, $title, $videoDate);
                    }
                }

                $currPage++;
            } //
        }
    }

    public function crawlDetail($url)
    {
        $html = $this->downloader->getHtml($url);
        if (! $html) {
            Logging::error("get data empty");
            return false;
        }
        $videoUrl = $this->parser->getDetail($html);

        return $videoUrl;
    }
}
