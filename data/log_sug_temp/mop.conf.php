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

class Mconfig
{
    const API_PRODUCT   = 'mp3';

    const API_XMLNS     = 'http://openapi.baidu.com/rest/2.0';

    const DEFAULT_FORMAT    = 'json';   //default output format

    const CURL_RETRY        = 3;        //crul retry times
    const CURL_CONN_TIMEOUT = 1000;     //curl connect timeout, ms
    const CURL_TIMEOUT      = 3000;     //curl timeout, ms

    const SOCKET_CONN_TIMEOUT   = 100;  //ms
    const SOCKET_READ_TIMEOUT   = 3000;
    const SOCKET_WRITE_TIMEOUT  = 100;

    const MOP_EC_INVALID_METHOD     = 100;
    const MOP_EC_CLASS_NOTFOUND     = 101;
    const MOP_EC_GET_DATA_FAIL      = 102;
    const MOP_EC_MISSING_PARAMETER  = 103;
    const MOP_EC_INVALID_FORMAT     = 104;
    
    const DEFAULT_PAGE_SIZE         = 10;
    const DEFAULT_PAGE_NO           = 1;

    /******************* 错误码异常处理 *********************/
    public static $arrErrMsg = array(
        self :: MOP_EC_INVALID_METHOD       => 'Unsupported openapi method',
        self :: MOP_EC_CLASS_NOTFOUND       => 'Class file not found',
        self :: MOP_EC_GET_DATA_FAIL        => 'Failed to get data',
        self :: MOP_EC_MISSING_PARAMETER    => 'Insufficient parameters',
        self :: MOP_EC_INVALID_FORMAT       => 'Unsupported output format, only json or xml'
    );

    public static $arrMethodList = array(
        'list_bangdan',
        'info_song',
        'info_singer',
        'info_album',
        'info_correction',
        'info_suggestion',
    	'info_mixsong',
        'info_lyrics',
        'search_music',
        'search_lyrics',
        'recommend_music'
    );
    /*********************************************************/

	public static $arrAPIXsdElts = array(
		'info_song_response' => 'song',
		'song'	=> 'item',
		'singer'	=> 'item',
		'album'	=> 'item'
	);

    /******************** socket服务相关配置 *****************/
    public static $arrSocketServer = array(
        'sts' => array(
            'server' => array(
                'tc' => array(
                    '10.26.36.36:26880',
                    '10.26.36.37:26880',
                    '10.26.36.38:26880',
                    '10.26.36.39:26880',
                    '10.26.36.40:26880',
                    '10.26.42.14:26880',
                    '10.26.42.15:26880',
                    '10.26.103.32:26880',
                    '10.26.103.33:26880',
                    '10.26.103.34:26880'
                ),
                'jx' => array(
                    '10.36.38.19:26880',
                    '10.36.38.20:26880',
                    '10.36.38.21:26880',
                    '10.36.38.22:26880',
                    '10.36.38.31:26880',
                    '10.36.38.32:26880',
                    '10.36.38.33:26880',
                    '10.36.41.24:26880',
                    '10.36.41.43:26880',
                    '10.36.41.44:26880'
                ),
                'test' => array(
                    'db-testing-mp321.db01.baidu.com:26880',
                )
            ),
            'ctime' => 100,
            'rtime' => 2000,
            'wtime' => 100
        ),
        'imgsvr' => array(
            'server' => array(
                'tc' => array(
                    'tc-ting-se02.tc.baidu.com:8205',
                    'tc-ting-se03.tc.baidu.com:8205'
                ),
                'jx' => array(
                    'yf-ting-se02.yf01.baidu.com:8205',
                    'yf-ting-se03.yf01.baidu.com:8205'
                ),
                'test' => array(
                    'tc-ting-se02.tc.baidu.com:8205'
                )
            )
        ),
        'tids' => array(
            'server' => array(
                'tc' => array(
                    '10.26.73.11:8105',
                    '10.26.76.31:8105'
                ),
                'jx' => array(
                    '10.36.42.24:8105',
                    '10.38.14.31:8105'
                ),
                'test' => array(
                    '10.26.73.11:8105'
                )
            )
        ),
        'ls' => array(
            'server' => array(
                'tc' => array(),
                'jx' => array()
            )
        ),
        'ids' => array(
            'server' => array(
                'tc' => array(),
                'jx' => array()
            )
        ),
        'rs' => array(
            'server' => array(
                'tc' => array(
                    '10.26.36.36:3014',
                    '10.26.36.37:3014',
                    '10.26.36.38:3014',
                    '10.26.36.39:3014',
                    '10.26.36.40:3014',
                    '10.26.42.14:3014',
                    '10.26.42.15:3014',
                    '10.26.103.32:3014',
                    '10.26.103.33:3014',
                    '10.26.103.34:3014'
                ),
                'jx' => array(
                    '10.36.38.19:3014',
                    '10.36.38.20:3014',
                    '10.36.38.21:3014',
                    '10.36.38.22:3014',
                    '10.36.38.31:3014',
                    '10.36.38.32:3014',
                    '10.36.38.33:3014',
                    '10.36.41.24:3014',
                    '10.36.41.43:3014',
                    '10.36.41.44:3014'
                ),
                'test' => array(
                    'db-apptest-mp305.vm.baidu.com:3014'
                )
            )
        )
    );
    /******************************************************************/
}
define('DATEYMD', date('Ymd'));
define('DS', DIRECTORY_SEPARATOR);
$arrConfig['logConfig'] = array(
    'intLevel'      => 8,
    'strLogFile'    => ROOT_PATH . DS . 'logs' . DS . 'sug.log.' . DATEYMD . date('H'),
    'strWfLogFile'  => ROOT_PATH . DS . 'logs' . DS . 'sug.log.wf.' . DATEYMD . date('H'),
);
?>
