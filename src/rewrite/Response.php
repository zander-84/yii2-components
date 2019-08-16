<?php
/**
 * Created by PhpStorm.
 * User: marvin
 * Date: 2018/7/29
 * Time: 09:21
 */

namespace zander84\helpers\rewrite;


class Response extends \yii\web\Response
{
    //____ 固定 code标志
    public $codeSucess = 0;
    public $codeuserSpaceError = 1;
    public $codeSystemSpaceError = 2;
    public $codeTokenError = 3;
    public $codeInlegalQeq = 4;
    public $codeMaintaining = 5;


    public $logCategory = false;
    public $logEnable = false;

    public $code = 0;
    public $msg = '';
    public $debugData = [];

    public $format = 'json';
    public $charset = 'UTF-8';
    public $formatters = [
        'json' => [
            'class' => 'yii\web\JsonResponseFormatter',
            'prettyPrint' => YII_DEBUG,
            'encodeOptions' => JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        ],
    ];

    public $msgs = [

    ];


    public function init ()
    {
        //____ 常见状态码说明
        if (!$this->msgs) {
            $this->msgs = [
                $this->codeSucess => 'Success',
                $this->codeuserSpaceError => '用户空间错误',
                $this->codeSystemSpaceError => '系统空间错误',
                $this->codeTokenError => '请登入',
                $this->codeInlegalQeq => '无效请求', //加密
                $this->codeMaintaining => '系统升级中',
            ];
        }

        $this->on('beforeSend', [$this, 'beforeSend']);
        parent::init();
    }

    public function beforeSend ($event)
    {

        $response = $event->sender;
        $debug_msg = $response->data ? $response->data : '';
        $data = '';


        if ($response->getIsSuccessful()) {
            $code = $response->code;
            $data = $debug_msg;
            $debug_msg = '';
        } elseif ($response->getIsClientError() && $response->statusCode == 401) {
            $code = $this->codeTokenError;

        } elseif ($response->getIsClientError() && $response->statusCode == 400) {
            $code = $response->code != 0 ? $response->code : $this->codeuserSpaceError;

        } elseif ($response->getIsClientError()) {
            $code = $response->code != 0 ? $response->code : $this->codeuserSpaceError;
            $response->msg = $response->msg ? $response->msg : (isset($debug_msg['message']) ? $debug_msg['message'] : '');

        } elseif ($response->getIsServerError()) {
            $code = $this->codeSystemSpaceError;

        } else {
            $code = $this->codeSystemSpaceError;

        }

        $msg = $response->msg ? $response->msg : (isset($this->msgs[$code]) ? $this->msgs[$code] : '');

        //日志
        if ($this->logEnable && $this->logCategory && $code == $this->codeSystemSpaceError) {
            \Yii::error($debug_msg, $this->logCategory);
        }

        if (YII_RESPONSE_DEBUG) {
            $response->data = [
                'code' => $code,                                // 状态码
                'msg' => $msg,                                  // 打印错误消息
                'data' => $data,                                // 数据返回
                'debug_http_code' => $response->statusCode,     // http code
                'debug_data' => $response->debugData,           // 用户打印调试输出
                'debug_msg' => $debug_msg,                      // 系统日志
            ];
        } else {
            $msg = $code == $this->codeSystemSpaceError ? '' : $msg;
            $response->data = [
                'code' => $code,
                'msg' => $msg,
                'data' => $data
            ];
        }


    }
}
