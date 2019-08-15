<?php
/**
 * Created by PhpStorm.
 * User: marvin
 * Date: 2018/7/29
 * Time: 09:21
 */

namespace zander84\helpers\rewrite;


class Request extends \yii\web\Request
{
    public $enableCsrfCookie = false;
    public $enableCsrfValidation = false;

    public $parsers = [
        'application/json' => 'yii\web\JsonParser',
    ];

    //'trustedHosts'=>['100.120.0.0/16' => ['X-Forwarded-For']]
    //'ipHeaders'=>['remoteip']//remoteip
}
