<?php

class Downloader
{
    static $lastTime;
    public function getHtml($url)
    {
        $header = array();
        $ip = $this->getRandIp();
        $header[] = "Accept-Language: zh-CN,zh;q=0.9,en;q=0.8";
        $header[] = "X_FORWARDED_FOR:".$ip;
        $header[] = "User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.97 Safari/537.36";
        $header[] = "Referer:".$url;
        $header[] = "CLIENT-IP:".$ip;
        $header[] = "Connection: keep-alive";
        $header[] = "Keep-Alive: 300";
        $header[] = "text/html; charset=utf-8";
        $header[] = "Host:".Config::$host;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        if (Config::$proxy) {
            curl_setopt($ch, CURLOPT_PROXY, Config::$proxy);
        }
        $errorMsg = '';
        $retry = 3;
        $data = curl_exec($ch);
        while (curl_errno($ch) && (--$retry>0)) {
            Logging::error('retry..');
            $data = curl_exec($ch);
        }
        if (curl_errno($ch)) {
            $errorMsg = curl_error($ch);
        }
        curl_close($ch);

        if (! $errorMsg) {
            return $data;
        } else {
            throw new \Exception("get html error,no data! error_message:".$errorMsg."\n");
        }
    }

    public function downloadVideo($url, $fileName, $date)
    {
        ini_set('memory_limit', Config::$memoryLimit);  // 调整最大占用内存

        $code = ['"', '*', ':', '<', '>', '？', '/', '\\', '|'];
        $fileName = preg_replace('# #', '', $fileName);
        $fileName = str_replace($code, '', $fileName);
        $fileName = str_replace(' ', '%20', $fileName);
        if (! is_dir(Config::$path)) {
            mkdir(Config::$path);
        }
        $filePath = Config::$path.'/'.date('Ymd', strtotime($date)).'_'.$fileName.'.mp4';
        if (file_exists($filePath)) {
            Logging::error('mp4 file exists');
            return;
        }
        $header = array();
        $ip = $this->getRandIp();
        $header[] = "User-Agent:Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36Name'";
        $header[] = "Referer:".$url;
        $header[] = "X_FORWARDED_FOR:".$ip;
        $header[] = "CLIENT-IP:".$ip;
        $fp = fopen($filePath, "w+");
        // Downloading a large file using curl: https://stackoverflow.com/questions/6409462/downloading-a-large-file-using-curl
        $ch = curl_init();
        // 从配置文件中获取根路径
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 900);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        if (Config::$proxy) {
            curl_setopt($ch, CURLOPT_PROXY, Config::$proxy);
        }
        // 开启进度条
        curl_setopt($ch, CURLOPT_NOPROGRESS, false);
        // 进度条的触发函数
        curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, array(new self, 'progress'));
        // ps: 如果目标网页跳转，也跟着跳转
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        $data = curl_exec($ch);
        curl_close($ch);
        fclose($fp);

        // 使用 rclone 上传 onedrive，其中 “91porn:/91porn” 对应网盘名称和路径
        // $command = 'rclone move -P '.$filePath.' 91porn:/91porn';
        // system($command);
        unset($data);
    }

    /**
     * 进度条下载.
     *
     * @param $ch
     * @param $downloadSize int 总下载量
     * @param $downloaded int 当前下载量
     * @param $uploadSize
     * @param $uploaded
     */
    function progress($resource, $downloadSize = 0, $downloaded = 0, $uploadSize = 0, $uploaded = 0)
    {
        if ($downloadSize === 0) {
            return;
        }
        if ($downloaded == $downloadSize) {
            Logging::progress(sprintf(" download finish: %.1f%%, %.2f MB/%.2f MB\n", $downloaded/$downloadSize*100, $downloaded/1000000, $downloadSize/1000000));
            return;
        }

        if (microtime(true)-Downloader::$lastTime < 1) {
            return;
        }

        Downloader::$lastTime = microtime(true);

        $downloaded = $downloaded/1000000;
        $downloadSize = $downloadSize/1000000;

        $progress = $downloaded/$downloadSize*100;
        Logging::progress(sprintf(" progress: %.1f%%, %.2f MB/%.2f MB"."\r", $progress, $downloaded, $downloadSize));
    }

    function getRandIp()
    {
        return rand(50, 250).".".rand(50, 250).".".rand(50, 250).".".rand(50, 250);
    }
}
