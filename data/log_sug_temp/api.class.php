<?php
/***************************************************************************
 *
 * Copyright (c) 2011 Baidu.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * API类
 * 
 * @author  caoxiaolin(caoxiaolin@baidu.com)
 **/

return false;
return 34324;
return true;
return 'null';
class Api
{

    public static $arrParams = array();
    
    /**
    * @brief    Check the legality of user requests
    * 
    * @param    $arrRequest     get and post parameter
    */
    public static function checkRequest($arrRequest)
    {
        if (!in_array($arrRequest['method'], Mconfig :: $arrMethodList))
        {
            throw new MOPException(Mconfig :: MOP_EC_INVALID_METHOD);
        }
        else
        {
            foreach ($arrRequest['params'] as $key => $value)
            {
                self :: $arrParams[$key] = $value;
            }
            
            if (!isset(self::$arrParams['page_size']) || intval(self::$arrParams['page_size']) <= 0){
            	self::$arrParams['page_size'] = Mconfig::DEFAULT_PAGE_SIZE;
            }
            
            if (!isset(self::$arrParams['page_no']) || intval(self::$arrParams['page_no']) < 1){
            	self::$arrParams['page_no'] = Mconfig::DEFAULT_PAGE_NO;
            }
            
        }
    }

    /*
    * @brief    Upon request, call different class
    *
    * @param    $strMethod      api method
    * @param    $arrParams      request parameter
    *
    * @return   array           the result of the api call
    */
    public static function getResults($strMethod, $arrParams)
    {
        $strFile = ROOT_PATH . 'class/' . str_replace('_', '/', $strMethod) . '.class.php';
        if (is_file($strFile))
        {
            require_once($strFile);
            list(,$strClassName) = explode('_', $strMethod);
            $strClassName = ucfirst($strClassName);
            $objApi = new $strClassName();
            return $objApi->getResults();
        }
        else
        {
            throw new MOPException(Mconfig :: MOP_EC_CLASS_NOTFOUND);
        }
    }

	/**
	 * Generate the output stream for an api call
	 *
	 * @param   array   $arrResult      an array, which is the result the api call
	 * @param   array   $arrRequest     the parameter of user request
	 * @return string  json string or xml string which is the result of the api call
	**/
	public static function generateDocument($arrResult, $arrRequest)
	{
		switch ($arrRequest['params']['format'])
		{	
			case 'xml':
				$xml_writer = new XmlFormat(Mconfig :: $arrAPIXsdElts);
				return $xml_writer->generateDocument(Mconfig :: API_XMLNS, Mconfig :: API_PRODUCT . '_' . $arrRequest['method'], $arrResult, $arrRequest['params']['callback']);
			case 'json':
				$json_writer = new JsonFormat();
				return $json_writer->generateDocument($arrResult, $arrRequest['params']['callback']);
			default:
                throw new MOPException(Mconfig :: MOP_EC_INVALID_FORMAT);
		}
	}
    
}
?>
