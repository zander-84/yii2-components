<?php
/**
 * Created by PhpStorm.
 * User: marvin
 * Date: 2019-05-14
 * Time: 11:24
 */
namespace zander84\helpers\helpers\wechat\lib\paydata;

/**
 *
 * 回调回包数据基类
 *
 **/
class WxPayNotifyResults extends WxPayResults
{
    /**
     * 将xml转为array
     * @param WxPayConfigInterface $config
     * @param string $xml
     * @return WxPayNotifyResults
     * @throws WxPayException
     */
    public static function Init($config, $xml)
    {
        $obj = new self();
        $obj->FromXml($xml);
        //失败则直接返回失败
        $obj->CheckSign($config);
        return $obj;
    }
}
