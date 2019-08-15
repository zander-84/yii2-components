<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace zander84\helpers\controllers\filters;

use Yii;
use yii\base\ActionFilter;

/**
 * for test
 * $data = [ 'time'=>'123'];
 * ksort($data);
 * $sign = strtoupper(md5(urldecode(http_build_query($data)).'ssNNGT5Awl'));
 * print_r([ 'time'=>'123','sign'=>$sign]);
 */


class CheckSign extends ActionFilter
{

    public $salt = 'ssNNGT5Awl';
    public $sign = 'sign';


    public $mustCheckSign = [];
    public $optionalCheckSigns = [];


    public function beforeAction ($action)
    {

        if ($this->mustCheckSign && is_array($this->mustCheckSign)) {

            $action_id = Yii::$app->controller->action->id;
            $request = Yii::$app->request;

            if (in_array($action_id, $this->mustCheckSign)) {

                if ($request->isOptions) {
                    return true;
                }

                if ($request->isGet && $this->checkSign($request->get())) {
                    return true;

                } else if ($request->isPost && $this->checkSign($request->post())) {
                    return true;

                } else {
                    Yii::$app->response->code = Yii::$app->response->codeInlegalQeq;
                    return false;
                }

            }

        }

        return true;
    }


    public function getSign ($arr)
    {
        //去除数组的空值
        array_filter($arr);
        if (isset($arr[$this->sign])) {
            unset($arr[$this->sign]);
        }
        //排序
        ksort($arr);
        //组装字符
        $str = $this->arrToUrl($arr)  . $this->salt;

        //使用md5 加密 转换成大写
        return strtoupper(md5($str));
    }


    public function arrToUrl ($arr)
    {
        return urldecode(http_build_query($arr));
    }

    //校验签名
    public function checkSign ($arr)
    {

        if (!isset($arr[$this->sign])) {
            return false;
        }

        $sign = $this->getSign($arr);

        if ($sign == $arr[$this->sign]) {
            return true;
        } else {
            return false;
        }
    }
}
