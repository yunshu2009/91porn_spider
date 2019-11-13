<?php

use DiDom\Document;
use DiDom\Query;

class Parser
{
    public function getList($html)
    {
        $listPage = new Document($html);
        $list = $listPage->find('//*[@class="listchannel"]/a[1]', Query::TYPE_XPATH);

        $arr = [];
        foreach ($list as $item) {
            $arr[] = [
                'title' => $item->getAttribute('title'),
                'detailUrl' => $item->getAttribute('href')
            ];
        }

        return $arr;
    }

    public function getDetail($html)
    {
        $page = new Document($html);
        try {
            $videoUrl = "";
            // 先直接取 source
            $source = $page->first('#vid source');
            if ($source) {
                $videoUrl = $source->getAttribute('src');
                Logging::debug("==== source ====\n");
            }

            // 分享链接也没有的话再解密
            if (! $videoUrl && ($js=$page->first('#vid script'))) {
                $cipher = $js->text();
                $videoUrl = $this->decodeJs($cipher);
                Logging::debug("====js decode ====\n");
            }
            // 如果 source 取不到就找分享链接
            if (! $videoUrl && ($shareLink = $page->first('#linkForm2 #fm-video_link'))) {
                $videoUrl = $shareLink->text();

                $html = (new Downloader())->getHtml($videoUrl);
                $sharePage = new Document($html);
                $videoUrl = $sharePage->first('source')->getAttribute('src');
                Logging::debug("==== sharelink ====\n");
            }
            $videoDate = $page->find('//*[@id="videodetails-content"]/span[2]', Query::TYPE_XPATH)[0]->text();

            return [$videoUrl, $videoDate];
        } catch(Exception $e) {
            Logging::debug("not found this video, please check if should use proxy");
        }
    }

    function decodeJs($cipher)
    {
        $js = (new Downloader())->getHtml('http://'.Config::$host.'/js/md5.js');
        $file = fopen('./md5.js',"w+");
        fputs($file,$js.'console.log(strencode(process.argv[2], process.argv[3], process.argv[4]));');// 写入文件
        fclose($file);
        $cipher = substr($cipher, 55);
        $cipher = substr($cipher, 0, strlen($cipher)-19);
        $cipher = str_replace('","', ' ', $cipher);
        $tag = shell_exec('node ./md5.js '.$cipher);
        $videoUrl = explode("<source src='", $tag)[1];
        $videoUrl = explode("' type='video/mp4", $videoUrl)[0];

        return $videoUrl;
    }
}