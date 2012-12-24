<?php
/***************************************************************************
 *
 * Copyright (c) 2011 Baidu.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * Class for packaging results as JSON
 */
 
class JsonFormat
{
    /**
     * @brief   Generate JSON string for result
     * @param   mix $result
     * @param   string $callback
     * @return  string
     */
    public function generateDocument($result, $callback = null)
    {
        //$json = $this->renderObject($result);
        $json = json_encode($result);;

        if ($callback)
        {
            $callback = preg_replace('/[^\w_\.()]/', '', $callback);
            $json = $callback . '(' . $json . ');';
        }

        return $json;
    }

    /**
     * @brief   Takes care of JSON rendering for the REST-based API. We do this ourselves
     *          so that we can properly handle our special quirks like invisibility.
     *
     * @param   mix $object Data to be rendered as json encoding
     * @return  string
     */
    protected function renderObject($object)
    {
        //return json_encode($object);
        $json = '';

        if (is_array($object))
        {
            $list = (key($object) === 0);
            $json .= $list ? '[' : '{';
            if (!empty($object))
            {
                $values = array();
                foreach ($object as $k => $v)
                {
                    $val = '';
                    if (!$list)
                    {
                        $val .= json_encode($k) . ':';
                    }
                    $val .= $this->renderObject($v);
                    $values[] = $val;
                }
                $json .= implode(',', $values);
            }
            $json .= $list ? ']' : '}';
        }
        elseif (is_object($object))
        {
            $json .= '{';
            $values = array();
            foreach ($object as $k => $v)
            {
                if (isset($v))
                {
                    $values[] = json_encode($k) . ':' . $this->renderObject($v);
                }
            }
            $json .= implode(',', $values);
            $json .= '}';
        }
        else
        {
            if (is_utf8($object))
            {
                $json .= json_encode($object);
            }
            else
            {
                $utf8 = gbk_to_utf8($object, UCONV_INVCHAR_ERROR);
                if ($utf8 === false)
                {
                    //convert failed, treat it as utf8
                    $json .= json_encode($object);
                }
                else
                {
                    $json .= json_encode($utf8);
                }
            }
        }
        return $json;
    }
}
?>
