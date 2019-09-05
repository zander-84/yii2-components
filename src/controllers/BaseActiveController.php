<?php

namespace zander84\helpers\controllers;


use Yii;

use ethercreative\ratelimiter\RateLimiter;
use yii\filters\auth\CompositeAuth;
use yii\rest\ActiveController;
use yii\filters\ContentNegotiator;
use yii\filters\VerbFilter;

use zander84\helpers\controllers\filters\CheckSign;
use zander84\helpers\controllers\filters\CustomQueryParamAuth;
use zander84\helpers\controllers\filters\Maintain;
use zander84\helpers\controllers\filters\Rsa;
use zander84\helpers\rewrite\Response;


class BaseActiveController extends ActiveController
{

    public $modelClass = 'app\models\Customers';
    protected $cors = [
        'Origin' => ['*'],
        'Access-Control-Allow-Origin' => ['*'],
        'Access-Control-Request-Method' => ['GET', 'POST', 'OPTIONS'],
    ];
    protected $verbs = [];
    protected $mustLogin = [];
    protected $optionalLogin = [];
    protected $mustCheckSign = [];

    protected $mustEncrypt = [];
    protected $optionalEncrypt = [];

    protected $rateLimit = 100;
    protected $timePeriod = 20;

    protected $signSalt = 'ssNNGT5Awl';
    protected $signKey = 'sign';



    public function actions ()
    {
        return [];
    }

    public function behaviors ()
    {
        $behaviors = parent::behaviors();

        $behaviors['maintain'] = [
            'class' => Maintain::class,
        ];
        $behaviors['rateLimiter'] = [
            'class' => RateLimiter::class,
            'rateLimit' => $this->rateLimit,
            'timePeriod' => $this->timePeriod,
            'separateRates' => false,
            'enableRateLimitHeaders' => false,
        ];

        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::class,
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
                'application/xml' => Response::FORMAT_JSON,
            ],
        ];

        $behaviors['verbFilter'] = [
            'class' => VerbFilter::class,
            'actions' => $this->verbs,
        ];

        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::class,
            'cors' => $this->cors,
        ];

        $behaviors['rsa'] = [
            'class' => Rsa::class,
            'mustEncrypt' => $this->mustEncrypt,
            'optionalEncrypt' => $this->optionalEncrypt,
            'publicKey' => $this->rsaPublicKey,
            'privateKey' => $this->rsaPrivateKey,
        ];

        $behaviors['checkSign'] = [
            'class' => CheckSign::class,
            'mustCheckSign' => $this->mustCheckSign,
            'salt' => $this->signSalt,
            'sign' => $this->signKey
        ];


        $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
            'only' => $this->mustLogin,
            'optional' => $this->optionalLogin,
            'authMethods' => [
                CustomQueryParamAuth::class,
            ],
        ];

        return $behaviors;
    }


    public function success($msg='')
    {
        Yii::$app->response->code = Yii::$app->response->codeSucess;
        Yii::$app->response->msg = $msg;
        return true;
    }

    public function userError($errors)
    {
        Yii::$app->response->code = Yii::$app->response->codeUserSpaceError;
        Yii::$app->response->msg = $errors;
        return true;
    }



    public function systemError($errors)
    {
        Yii::$app->response->code = Yii::$app->response->codeSystemSpaceError;
        Yii::$app->response->msg = $errors;
        return true;
    }

















    protected $rsaPublicKey = '-----BEGIN PUBLIC KEY-----
MIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEAvyc7CZgyZFWrb2xPhb1L
ACP4viyOi7lHPr5zASBPFPC9u3kCgJcSS9P4h8iYB0h6SLnr0J4QWfr8Jrpn8kWr
x/ez2QDMncORAm8qynvkrPGl/7Lm+5bwhOxCMHEiWFuzaNEVmLPdQeV7TGxbnaJT
9UfuyTWkO9RttDSUvNcv0AI0UG904uC5BkXLgS8kw8hSvYzRPePI0El85pkVBTwo
Ts4lateWXGjW3iXRtJoNLblFGyD+qaUm2HuWC3U+JugnrHBnh9nLvTT5PRlXVaH4
IVRDCYF+ohQ4RVtyIbXeiYjO5SbL9mf65aVg1A1d9W7JZQ7TwNQq958ZTrAWVWYK
rjvdsaAlbd1ubDWoiMLckF+nWZW8zqY6BlnXa88/aKG6elcfFrx9YMd9pZLbh9Mg
iZYRWdJxvN3lf0KRQJD/IiJC2cnLkzjEmkwOekuQlPhHhOmqxpyRLP7Ndc3tFrWt
mF3dMiVphHInvIbCujU79mmxshkevy94AdqQ21Kz61ZlY54zB3itfpXa5/UosKNN
Iyf0LfDIgmrnVHgToqPZGHQgVuWshj63lSQ0YrCalAlfHnfTCSG9h0QtDQgytjQj
zujpTvTpUN9BcaFzmHkf4IdY2tZ+/K2FDxiPihO8tPiVdKP6kzq8Xb72HambWdCy
7Iz7mkexVrun17jju3fdX3kCAwEAAQ==
-----END PUBLIC KEY-----';

    protected $rsaPrivateKey = '-----BEGIN PRIVATE KEY-----
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
c5Qs1Yo3kgj1hNVIli/As8eeqjCjG1E=
-----END PRIVATE KEY-----';

}
