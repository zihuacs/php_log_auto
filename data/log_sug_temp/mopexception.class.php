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

class MOPException extends Exception
{
    public function __construct($intErrCode)
    {
        parent :: __construct(Mconfig :: $arrErrMsg[$intErrCode], $intErrCode);
    }
}
?>
