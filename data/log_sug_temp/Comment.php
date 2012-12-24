<?php
test();
function test() {return;}
class PHPParser_Comment
{
    
    public function __construct() {
        /**
        * 
        */
        class ClassName extends AnotherClass
        {
            
            function __construct()
            {
                # code...
                c();
                /**
                * 
                */
                class ClassName extends AnotherClass
                {
                    
                    function __construct()
                    {
                        # code...
                    }
                }
            }
        }
    }

    /**
     * Gets the comment text.
     *
     * @return string The comment text (including comment delimiters like /*)
     */
    public function getText() {
        return $this->text;
    }
    /**
     * Sets the comment text.
     *
     * @param string $text The comment text (including comment delimiters like /*)
     */
    public function setText($text) {
        $this->text = $text;

    }

    /**
     * Gets the line number the comment started on.
     *
     * @return int Line number
     */
    public function getLine() {
        return $this->line;
    }
}

class PHPParser_CC2
{
    
    public function __construct() {
    }

    /**
     * Gets the comment text.
     *
     * @return string The comment text (including comment delimiters like /*)
     */
    public function getText() {
        return $this->text;
    }
    /**
     * Sets the comment text.
     *
     * @param string $text The comment text (including comment delimiters like /*)
     */
    public function setText($text) {
        $this->text = $text;
    }

    /**
     * Gets the line number the comment started on.
     *
     * @return int Line number
     */
    public function getLine() {
        return $this->line;
    }
}



/**
 * DapperPHP (php轻量级框架)
 * socket接口操作类库
 * @package     Socket
 * @author      zhaoshunyao <zhaoshunyao@baidu.com>
 * @since       2010-12-06
 */
class Dapper_Model_Socket
{
    public static $socket = null;

    public static function socketConnect($arrConfig, $ctime = 1000, $rtime = 5000, $wtime = 5000)
    {
    }
    
    public static function socketWrite($data, $dataLen)
    {
    }
    
    /**
     * socket读数据
     *
     * @param int $dataLen
     * @return false/$data
     */
    public static function socketRead($dataLen)
    {
    }
}