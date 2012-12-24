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
 * @changer yangyj 11.11
 **/

define('MANUAL_INTERVENTION', 1);

class Suggestion
{
    const HTTP_SERVICE_URL = 'http://nssug.baidu.com/su';   //qa: 10.81.13.225:8333   rd: bb-rank-testc004.vm.baidu.com:8338

    const SEPARATION_1 = '{#S+_}';
    const SEPARATION_2 = '{#}';

    const FIRST_NUMBER  = 10;   //第一次请求的条数
    const SECOND_NUMBER = 20;   //第二次请求的条数

    /** manual intervention */
    public static $manual_intervention = array(
        'keyword' => array(
            '？'         => 'eason',
            '？ 陈'      => 'eason',
            '？ 陈奕'    => 'eason',
            '？ 陈奕迅'  => 'eason',
            '?'          => 'eason',
            '? 陈'       => 'eason',
            '? 陈奕'     => 'eason',
            '? 陈奕迅'   => 'eason',
            '陈奕迅 ？'  => 'eason',
            '陈奕迅 ?'   => 'eason',
        ),

        'manual_data' => array(
            'eason' => array(
                'song_data' => array(
                    0 => array(
                        'songid'    => '12267386',
                        'songname'  => '积木',
                        'artistname'=> '陈奕迅',
                    ),
                    1 => array(
                        'songid'    => '11779364',
                        'songname'  => '孤独患者',
                        'artistname'=> '陈奕迅',
                    ),
                    2 => array(
                        'songid'    => '12274249',
                        'songname'  => '还要不要走',
                        'artistname'=> '陈奕迅',
                    ),
                    3 => array(
                        'songid'    => '11386715',
                        'songname'  => '看穿',
                        'artistname'=> '陈奕迅',
                    ),
                    4 => array(
                        'songid'    => '11276024',
                        'songname'  => '神奇化妆师',
                        'artistname'=> '陈奕迅',
                    ),
                    5 => array(
                            'songid'    => '11387190',
                            'songname'  => '内疚',
                            'artistname'=> '陈奕迅',
                    ),
                    6 => array(
                            'songid'    => '11780318',
                            'songname'  => 'Baby Song',
                            'artistname'=> '陈奕迅',
                    ),
                    7 => array(
                            'songid'    => '11386710',
                            'songname'  => '张氏情歌',
                            'artistname'=> '陈奕迅',
                    ),

                ),

                'album_data'=> array(
                    0 => array(
                        'albumid'  => '11386707',
                        'albumname'=> '？',
                        'artistname'=> '陈奕迅', 
                        'artistpic' => 'b3508d13773d40f56538db9e',
                    ),
                ),
            ),
        ),
    );
    
    /**
    * @brief   获取http内容并解析json
    *
    * @param    $strUrl     http url
    * @param    $intTimes   第几次请求
    *
    * @return   array 
    */
    public static function getResultContents($strUrl, $intTimes = 1)
    {
        $strContent = Utils :: getContentsFromUrl($strUrl);
        if ($strContent === false)
        {
            Log :: warning(sprintf('%s: get json content fail from url %s', __METHOD__, $strUrl));
            throw new MOPException(Mconfig :: MOP_EC_GET_DATA_FAIL);
            return false;
        }

        $strContent = str_replace(array('p:true,', 'p:false,', '\\\''),
                                  array('', '', '\''), $strContent);
        $jsonContent = mb_substr($strContent, mb_strlen('window.baidu.sug({q:"' . Api :: $arrParams['word'] . '",s:', 'utf-8'), -3, 'utf-8');
        $arrContent = json_decode($jsonContent);
        //print_r($arrContent);
        if ($arrContent !== false && is_array($arrContent))
        {
            foreach ($arrContent as $k => $item)
            {
                $arrItem = explode(self :: SEPARATION_1, $item);
                $arrContent[$k] = $arrItem[1];
            }
            $intCount = count($arrContent);
            $arrContent = array_unique($arrContent);
            if (count($arrContent) >= self :: FIRST_NUMBER || $intCount < self :: FIRST_NUMBER || $intTimes == 2)
            {
                return array_slice($arrContent, 0, 10);
            }
            elseif ($intTimes == 1)
            {
                $strUrl= str_replace('&su_num=' . self :: FIRST_NUMBER, '&su_num=' . self :: SECOND_NUMBER, $strUrl);
                return self :: getResultContents($strUrl, 2);
            }
        }
        else
        {
            Log :: warning(sprintf('%s: json_decode error, json content is %s', __METHOD__, $jsonContent));
            return false;
        }
    }
    public function getResults()
    {
        if (empty(Api :: $arrParams['word']))
        {
            throw new MOPException(Mconfig :: MOP_EC_MISSING_PARAMETER);
        }
        $logstr = "";
        foreach(API :: $arrParams as $key=>$value)
        {
            $logstr = $logstr.$key.":".$value."\t";
        }
        Log::notice(sprintf("query :%s",$logstr));
        $arrReturn = array();
        
        if (empty(Api :: $arrParams['fields']) || in_array('song', Api :: $arrParams['fields']))
        {
            //print_r(Api :: $arrParams);
            if (defined('MANUAL_INTERVENTION') && MANUAL_INTERVENTION)
            {
                $arrReturn = Suggestion :: manualProcess(Api :: $arrParams);
                if (count($arrReturn))
                {
                    Log :: notice("SUC: NO RESULT !!!");
                    return $arrReturn;
                }
            }
            
            $strUrl = self :: HTTP_SERVICE_URL . '?ie=utf-8&prod=ting_song&su_num=' . self :: FIRST_NUMBER . '&wd=' . rawurlencode(Api :: $arrParams['word']);
            $arrResults = self :: getResultContents($strUrl);
            if ($arrResults !== false && !empty($arrResults))
            {
                foreach ($arrResults as $item)
                {
                    $arrSongInfo = explode(self :: SEPARATION_2, $item);
                    $arrSong[] = array(
                        'songid'        => $arrSongInfo[0],
                        'songname'      => $arrSongInfo[1],
                        'artistname'    => $arrSongInfo[2]
                    );
                }
            }
            else
            {
                $arrSong = array();
            }
            
            $arrReturn['song'] = $arrSong;
        }
        
        if (empty(Api :: $arrParams['fields']) || in_array('artist', Api :: $arrParams['fields']))
        {
            $strUrl = self :: HTTP_SERVICE_URL . '?ie=utf-8&prod=ting_artist&su_num=' . self :: FIRST_NUMBER . '&wd=' . rawurlencode(Api :: $arrParams['word']);
            $arrResults = self :: getResultContents($strUrl);
            if ($arrResults !== false && !empty($arrResults))
            {
                /** remove repeat singer. */
                $aid_container = array();
                foreach ($arrResults as $item)
                {
                    $arrArtistInfo = explode(self :: SEPARATION_2, $item);
                    $aid = $arrArtistInfo[0];
                    if (! isset($aid_container[$aid]))
                    {
                        $arrArtist[] = array(
                            'artistid'      => $arrArtistInfo[0],
                            'artistname'    => $arrArtistInfo[1],
                            'artistpic'     => $arrArtistInfo[2],
                        );
                        
                        $aid_container[$aid] = 1;
                    }
                }
                //print_r($aid_container);
            }
            else
            {
                $arrArtist = array();
            }
            //print_r($arrArtist);exit;
            
            $arrReturn['artist'] = $arrArtist;
        }

        if (empty(Api :: $arrParams['fields']) || in_array('album', Api :: $arrParams['fields']))
        {
            $strUrl = self :: HTTP_SERVICE_URL . '?ie=utf-8&prod=ting_album&su_num=' . self :: FIRST_NUMBER . '&wd=' . rawurlencode(Api :: $arrParams['word']);
            $arrResults = self :: getResultContents($strUrl);
            if ($arrResults !== false && !empty($arrResults))
            {
                foreach ($arrResults as $item)
                {
                    $arrAlbumInfo = explode(self :: SEPARATION_2, $item);
                    $arrAlbum[] = array(
                        'albumid'       => $arrAlbumInfo[0],
                        'albumname'     => $arrAlbumInfo[1],
                        'artistname'    => $arrAlbumInfo[2],
                        'artistpic'     => $arrAlbumInfo[3]
                    );
                }
            }
            else
            {
                $arrAlbum = array();
            }
            
            $arrReturn['album'] = $arrAlbum;
        }
        //print_r($arrReturn);
        Log :: notice(sprintf("SUCC: song num:%d ablum num:%d aritst num:%d",count($arrSong),count($arrAlbum),count($arrArtist)));
        return $arrReturn;
    }

    public static function manualProcess($query)
    {
        $res = array();

        if (isset($query['word']) && isset(Suggestion::$manual_intervention['keyword'][$query['word']]))
        {
            $manual_data_key = Suggestion::$manual_intervention['keyword'][$query['word']];

            $res['song'] = Suggestion::$manual_intervention['manual_data'][$manual_data_key]['song_data'];
            $res['album']= Suggestion::$manual_intervention['manual_data'][$manual_data_key]['album_data'];
        }
        Log :: notice("manual processed !!!");
        return $res;
    }
}
?>
