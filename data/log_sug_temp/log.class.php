<?php
/**
 * @brief class for logging
 *
 * @PHP version 5+
**/
function a() {}
define('LOG_LEVEL_NONE',    0);
define('LOG_LEVEL_FATAL',   1);
define('LOG_LEVEL_WARNING', 2);
define('LOG_LEVEL_NOTICE',  4);
define('LOG_LEVEL_TRACE',   8);
define('LOG_LEVEL_DEBUG',   16);
define('LOG_LEVEL_ALL',     32);

define('LOG_MAX_FILE_SIZE', 1024000000);

class Log
{
    public $_arrLogLevels = array(
        LOG_LEVEL_NONE      => 'NONE',
        LOG_LEVEL_FATAL     => 'FATAL',
        LOG_LEVEL_WARNING   => 'WARNING',
        LOG_LEVEL_NOTICE    => 'NOTICE',
        LOG_LEVEL_TRACE     => 'TRACE',
        LOG_LEVEL_DEBUG     => 'DEBUG',
        LOG_LEVEL_ALL       => 'ALL',
    );

    protected $_intLevel;
    protected $_strLogFile;
    protected $_strWfLogFile;
    static $instance = null;
 
    private function __construct($arrLogConfig = array())
    {
        $this->_intLevel      = $arrLogConfig['intLevel'];
        $this->_strLogFile    = $arrLogConfig['strLogFile'];
        $this->_strWfLogFile  = $arrLogConfig['strWfLogFile'];
    }

    public static function getInstance()
    {
        if(self::$instance ==null){
            self::$instance  = new Log($GLOBALS['arrConfig']['logConfig']);        
        }
        return self::$instance;
    }

    public  function writeLog($intLevel, $str, $bolEcho = false)
    {
        if ($intLevel > $this->_intLevel) {
            return;
        }

        $strLevel   = $this->_arrLogLevels[$intLevel];
        if (strlen($strLevel) == 0) {
            $strLevel = $intLevel;
        }

        $strLogFile = ($intLevel > LOG_LEVEL_WARNING)
            ? $this->_strLogFile
            : $this->_strWfLogFile;
        if (strlen($strLogFile) == 0) {
            $strLogFile = sprintf('undefined.log.%s', date('Ymd'));
        }

        $str = sprintf("%s: %s: appui. * %u %s\n", $strLevel,
                date('m-d H:i:s'), LOG_ID, $str);
        if ($bolEcho === true) {
            echo "$str <hr>\n";
        }

        @clearstatcache();
        $arrFileStats = @stat($strLogFile);
        if (is_array($arrFileStats) && floatval($arrFileStats['size']) > LOG_MAX_FILE_SIZE) {
            unlink($strLogFile);
        }

        $fp = fopen($strLogFile, "a");
        if (flock($fp, LOCK_EX)) {      // 进行排它型锁定
             fwrite($fp, $str);
             flock($fp, LOCK_UN);       // 释放锁定
        }       
        fclose($fp);
    }

    public static function debug($str, $bolEcho = false)
    {
        $log = Log::getInstance();
        if (!is_object($log)) {
            return false;
        }
        return $log->writeLog(LOG_LEVEL_DEBUG, $str, $bolEcho);
    }

    public static function trace($str, $bolEcho = false)
    {
        $log = Log::getInstance();
        if (!is_object($log)) {
            return false;
        }
        return $log->writeLog(LOG_LEVEL_TRACE, $str, $bolEcho);
    }

    public static function notice($str, $bolEcho = false)
    {
        $log = Log::getInstance();
        if (!is_object($log)) {
            return false;
        }
        return $log->writeLog(LOG_LEVEL_NOTICE, $str, $bolEcho);
    }

    public static function warning($str, $bolEcho = false)
    {
        $log = Log::getInstance();
        if (!is_object($log)) {
            return false;
        }
        return $log->writeLog(LOG_LEVEL_WARNING, $str, $bolEcho);
    }

    public static function fatal($str, $bolEcho = false)
    {
        $log = Log::getInstance();
        if (!is_object($log)) {
            return false;
        }
        return $log->writeLog(LOG_LEVEL_FATAL, $str, $bolEcho);
    }
}

/* vim: set et ts=4 et: */

