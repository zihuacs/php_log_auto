<?php
/***************************************************************************
 *
 * Copyright (c) 2011 Baidu.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * here is description for this code.
 * 
 * @author  caoxiaolin(caoxiaolin@baidu.com)
 **/

class Utils
{
    /**
    * @brief    Parse request parameter
    */
    public static function getRequest()
    {
        $strUri = $_SERVER['REQUEST_URI'];
        $arrUrl = parse_url($strUri);
        $arrParams = explode('/', $arrUrl['path']);

        $arrRequest['method'] = $arrParams[1] . '_' . $arrParams[2];
        $arrRequest['params'] = array_merge($_GET, $_POST);

        if(isset($arrRequest['params']['word']))
        {
           $arrRequest['params']['word'] = trim($arrRequest['params']['word']);       
           $arrRequest['params']['word'] = stripslashes($arrRequest['params']['word']);
        }
        $arrRequest['params']['word'] = isset($arrRequest['params']['word']) ? 
            str_replace(array(
                '\'', '"', 
                '。', '“',
                '’', '”',
                '‘', '\\',
              ), 
              ' ', 
              $arrRequest['params']['word']) : '';
      //  $arrRequest['params']['word'] = preg_replace('/\s(?=\s)/',"",$arrRequest['params']['word']);
        if (isset($arrRequest['params']['scope']) && !empty($arrRequest['params']['scope']))
        {
            $arrRequest['params']['scope'] = explode(',', $arrRequest['params']['scope']);
        }
        else
        {
            $arrRequest['params']['scope'] = array();
        }

        if (isset($arrRequest['params']['fields']) && !empty($arrRequest['params']['fields']))
        {
            $arrRequest['params']['fields'] = explode(',', $arrRequest['params']['fields']);
        }
        else
        {
            $arrRequest['params']['fields'] = array();
        }

        if (!isset($arrRequest['params']['format']) || empty($arrRequest['params']['format']))
        {
            $arrRequest['params']['format'] = Mconfig :: DEFAULT_FORMAT;
        }

        return $arrRequest;
    }

    /**
    * @brief    get LogID
    */
    public static function getLogID()
    {
        date_default_timezone_set('Asia/Chongqing');
        $arr = gettimeofday();
        return ((($arr['sec']*100000 + $arr['usec']/10) & 0x7FFFFFFF) |
                0x80000000);
    }

    /**
    * @brief    获取当前机房配置,默认tc
    * @return   tc or jx
    */
    public static function getComputerRoom()
    {
        if (isset($_SERVER['HTTP_X_LOCATION']) && in_array($_SERVER['HTTP_X_LOCATION'], array('tc', 'jx')))
        {
            return trim($_SERVER['HTTP_X_LOCATION']);
        }
        else
        {
            return 'tc';
        }
    }
    
    /**
    * @brief    get client ip address
    */
    public static function getClientIP()
    {
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } elseif (isset($_SERVER["HTTP_CLIENTIP"])) {
            $ip = $_SERVER["HTTP_CLIENTIP"];
        } elseif (isset($_SERVER["REMOTE_ADDR"])) {
            $ip = $_SERVER["REMOTE_ADDR"];
        } elseif (getenv("HTTP_X_FORWARDED_FOR")) {
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        } elseif (getenv("HTTP_CLIENTIP")) {
            $ip = getenv("HTTP_CLIENTIP");
        } elseif (getenv("REMOTE_ADDR")) {
            $ip = getenv("REMOTE_ADDR");
        } else {
            $ip = "127.0.0.1";
        }

        $pos = strpos($ip, ',');
        if ($pos > 0) $ip = substr($ip, 0, $pos);
        return trim($ip);
    }

    public static function ip2num($strIp)
    {
        if (!preg_match('/^([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)$/is', $strIp))
        {
            return 0;
        }
        $intIp = ip2long($strIp);
        $intIp = (($intIp & 0xFF) << 24)
             | ((($intIp >> 8) & 0xFF) << 16)
             | ((($intIp >> 16) & 0xFF) << 8)
             | (($intIp >> 24) & 0xFF);
        
        return $intIp;
    }

    /**
    * @brief    get url contents from http server, throuth BaeFetchUrl
    */
    public static function getContentsFromUrl($strUrl, $arrParams = array())
    {
        $arrErrorUrl = array('http://mp3.baidu.com/error.html');
        
        $intConnTimeout     = isset($arrParams['conn_timeout']) ? $arrParams['conn_timeout'] : Mconfig :: CURL_CONN_TIMEOUT;
        $intTimeout         = isset($arrParams['timeout']) ? $arrParams['timeout'] : Mconfig :: CURL_TIMEOUT;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $intConnTimeout);      
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, $intTimeout);
        curl_setopt($ch, CURLOPT_URL, $strUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $html = curl_exec($ch);
        curl_close($ch);

        return $html;
    }

    public static function gbk_to_utf8($strWord)
    {
        return gbk_to_utf8($strWord, strlen($strWord));
    }

    public static function utf8_to_gbk($strWord)
    {
        return utf8_to_gbk($strWord, strlen($strWord));
    }

}

define('LOG_ID',                Utils :: getLogID());
define('CLIENT_IP',             Utils :: getClientIP());
define('CLIENT_IP_INT',         Utils :: ip2num(CLIENT_IP));
define('CURRENT_COMPUTER_ROOM', Utils :: getComputerRoom());

/**
 * debug function
 */
function debug($arry, $variable = '', $str = false, $exit = false)
{
    $html =  '<pre>'. ($variable ? $variable .' =  ' : '') . print_r($arry, true) .'</pre>';

    if ($str)
    {    
        return '<div class="php_debug">'. $html .'</div>'; 
    } else 
    {    
        echo $html;
    }    

    if ($exit)
    {    
        exit();
    }    
}
