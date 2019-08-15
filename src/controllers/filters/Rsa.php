<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace zander84\helpers\controllers\filters;


use Yii;
use yii\base\ActionFilter;

class Rsa extends ActionFilter
{

    public $publicKey='';

    public $privateKey='';

    public $mustEncrypt = [];
    public $optionalEncrypt = [];

    private $config = [
        "digest_alg" => "sha512",                   //hash_algos()
        "private_key_bits" => 4096,                 //  512 1024 2048  4096
        "private_key_type" => OPENSSL_KEYTYPE_RSA,
    ];


    //$mustEncrypt = ['actionid'=>'key'];
    public function beforeAction($action)
    {

        if( $this->mustEncrypt ){
            $action_id = Yii::$app->controller->action->id;
            if( isset($this->mustEncrypt[$action_id]) && $this->mustEncrypt[$action_id] ){
                $key = $this->mustEncrypt[$action_id];

                $request = Yii::$app->request;
                if($request->isGet){
                    $data = $this->decrypt($request->get($key));
                    if(!$data){
                        Yii::$app->response->code = Yii::$app->response->codeInlegalQeq;
                        return false;
                    }
                    $_GET[$key] = $data;

                }else{
                    $data = $this->decrypt($request->post($key));
                    if(!$data){
                        Yii::$app->response->code = Yii::$app->response->codeInlegalQeq;
                        return false;
                    }
                    $_POST[$key] = $data;
                }
            }

        }

        if ($this->optionalEncrypt){
            $action_id = Yii::$app->controller->action->id;
            if( isset($this->optionalEncrypt[$action_id]) && $this->optionalEncrypt[$action_id] ){
                $key = $this->optionalEncrypt[$action_id];

                $request = Yii::$app->request;
                if($request->isGet){
                    $_GET[$key] = $this->decrypt($request->get($key));
                }else{
                    $_POST[$key] = $this->decrypt($request->post($key));
                }
            }
        }

        return true;
    }


    public function generateKey()
    {
        $res = openssl_pkey_new($this->config);
        openssl_pkey_export($res, $private_key);
        $public_key = openssl_pkey_get_details($res);

        $this->privateKey = $private_key;
        $this->publicKey = $public_key['key'];

        return ['private_key' => $this->privateKey, 'public_key' => $this->publicKey];
    }


    //_________________________________________________________________________________________
    public function encrypt($data)
    {

        openssl_public_encrypt($data, $encrypted, $this->publicKey);
        return base64_encode($encrypted);
    }


    //_________________________________________________________________________________________
    public function decrypt($data)
    {
        $data = base64_decode($data);
        openssl_private_decrypt($data, $decrypted, $this->privateKey);
        return $decrypted;
    }


}
