<?php
/**
 * Created by PhpStorm.
 * User: Jone
 * Email: abc@jone.xyz
 * Date: 2021/8/5
 * Time: 23:03
 */


class Mp3
{
    var $str;
    var $time;
    var $frames;

    /**
     * 读取mp3文件，Create a new mp3
     * @param string $path
     */
    function __construct($path = "")
    {
        if ($path != "") {
            $this->str = file_get_contents($path);
        }
    }

    /**
     * 合并mp3文件, Put an mp3 behind the first mp3
     * @param Mp3 $mp3
     */
    public function mergeBehind(Mp3 $mp3)
    {
        $this->str .= $mp3->str;
    }

    // Calculate where's the end of the sound file
    private function getIdvEnd()
    {
        $strlen = strlen($this->str);
        $str = substr($this->str, ($strlen - 128));
        $str1 = substr($str, 0, 3);
        if (strtolower($str1) == strtolower('TAG')) {
            return $str;
        } else {
            return false;
        }
    }

    // Calculate where's the beginning of the sound file
    private function getStart()
    {
        $strlen = strlen($this->str);
        for ($i = 0; $i < $strlen; $i++) {
            $v = substr($this->str, $i, 1);
            $value = ord($v);
            if ($value == 255) {
                return $i;
            }
        }
    }

    // Remove the ID3 tags
    public function striptags()
    {
        //Remove start stuff...
        $newStr = '';
        $s = $start = $this->getStart();
        if ($s === false) {
            return false;
        } else {
            $this->str = substr($this->str, $start);
        }
        //Remove end tag stuff
        $end = $this->getIdvEnd();
        if ($end !== false) {
            $this->str = substr($this->str, 0, (strlen($this->str) - 129));
        }
    }

    // Display an error
    private function error($msg)
    {
        //Fatal error
        die('<strong>audio file error: </strong>' . $msg);
    }

    /**
     * 浏览器端输出文件 Send the new mp3 to the browser
     * @param $path string  文件名
     * @param $is_download bool  是否下载，不下载则在浏览器端播放
     * @return string
     */
    public function output($path,$is_download=false)
    {
        //Output mp3
        //Send to standard output
        if (ob_get_contents())
            $this->error('Some data has already been output, can\'t send mp3 file');
        if (php_sapi_name() != 'cli') {
            //We send to a browser
            header('Content-Type: audio/mpeg3');
            if (headers_sent())
                $this->error('Some data has already been output to browser, can\'t send mp3 file');

            header('Content-Length: ' . strlen($this->str));

            if($is_download)
                header('Content-Disposition: attachment; filename="' . $path . '"');
        }
        echo $this->str;
        return '';
    }

    /**
     * 保存文件 Save MP3
     * @param $filename string 保存后的文件名
     */
    public function saveMp3($filename)
    {
        $myfile = fopen($filename, "w") or die("Unable to open file!");
        fwrite($myfile, $this->str);
        fclose($myfile);
    }
}

// First File: (Google speech)
$mp3 = new Mp3('mp3/tts_0.mp3');
$mp3->striptags();
//Second file
$second = new Mp3("mp3/tts_1.mp3");
$mp3->mergeBehind($second);
$mp3->striptags();

//$mp3->output('word.mp3'); 下载文件
$mp3 ->output('word.mp3'); //保存文件
