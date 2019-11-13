<?php

$cipher = '
      	 <!--
            	  document.write(strencode("Znp2R1UbMiY/FBsbAQMCbjleAAYlcQxXOzZfCSN0NwYgNgkMYmEAHQguIDk4OkERBgAkOB8iFHggHhNpGglyJ1BrPhsUXhBgPR1YdCwiP0ohdRQoUX8VGmM6bSEXWi8YegJpIxdjGB8FBCpZYR0FVQIGOEEoAzoeCn0+fwgfBlMzYTEzbD0RExV3W0J4cVsa","62811CxLeGYabnOWslh6A9MawOgql0bsmLny/5MhEzmNtCxeeDuBSXYHmtBZUZGS3/o4wmAYnX42NHXxt1ZG2+CH9VTJtkGH7h/vB0BsUPoh/gH/LRS9fGkpC5lJkXSjyRkC6zGeYEj5","Znp2R1UbMiY/FBsbAQMCbjleAAYlcQxXOzZfCSN0NwYgNgkMYmEAHQguIDk4OkERBgAkOB8iFHggHhNpGglyJ1BrPhsUXhBgPR1YdCwiP0ohdRQoUX8VGmM6bSEXWi8YegJpIxdjGB8FBCpZYR0FVQIGOEEoAzoeCn0+fwgfBlMzYTEzbD0RExV3W0J4cVsa"));
      	 //-->';
$ret = decodeJs($cipher);
echo $ret;

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