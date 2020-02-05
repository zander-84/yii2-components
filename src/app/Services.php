<?php

namespace zander84\helpers\app;

use yii\base\BaseObject;
use yii\base\InvalidCallException;
use yii\base\InvalidConfigException;

use Yii;

/**
 * Class Services
 * @package zander84\helpers\app
 *
 *
 * config demo
    'services' => [
        'class' => 'Services',   // 全局服务  命名空间全局，主要来用写服务备注
        'childServices'=>[
            'activity' => [
                'class' => 'zander84\helpers\services\Activity',
                'childServices' => [
                    'sales' => [
                        'class' => 'zander84\helpers\services\Sales',
                        'enableService'=>true,
                        //'currentActivityInfo' => [
                        //    'id' => 5,
                        //    'title' => '国庆特惠'
                        //]
                    ],
                ],
            ],
        ],
    ],

    在index.php中引入：
    require(__DIR__ . '/../vendor/autoload.php');
    require(__DIR__ . '/../vendor/zander-84/yii2-components/src/app/Yii.php');
    $config = require(__DIR__ . '/../config/api.php');
    require(__DIR__ . '/../../basic/Services.php');   //需要先导入配置文件
    $app = new \yii\web\Application($config);
    $app->run();
 *
 *
 * Services文件内容：
 *
 *
 * 注释部分
 * @ property \zander84\helpers\services\activity $activity 备注服务
 *
 * 类部分
 *
    class Services extends zander84\helpers\app\Services {

    }

    //初始化服务：
    $servicesConf = $config['services'] ?? [];
    unset($config['services']);
    if (isset($servicesConf['class'])){
        $class = $servicesConf['class'];
        unset($servicesConf['class']);
        Yii::$service = new $class($servicesConf);
    }else{
        Yii::$service = new Services($servicesConf);
    }
 *
 *
 */
class Services extends BaseObject
{
    /**
     * @var array 服务的配置数组
     */
    public $childServices ;

    /**
     * @var array 实例化过的服务数组
     */
    protected $_childServices;

    /**
     * @var bool 是否启用
     */
    public $enableService;

    protected $_callFunc = [];

    /**
     * 根据服务名字获取服务实例
     * Get service instance by service name.
     *
     * 用类似于 Yii2 的 component 原理，采用单例模式实现的服务功能，
     * 服务的配置文件位于 config/services 目录
     *
     * @throws \yii\base\InvalidConfigException if the service is not found or the service is disabled
     * @var string $childServiceName
     */
    public function getChildService ($childServiceName)
    {
        if (!$this->_childServices[$childServiceName]) {
            $childServices = $this->childServices;
            if (isset($childServices[$childServiceName])) {
                $service = $childServices[$childServiceName];
                if (!isset($service['enableService']) || $service['enableService']) {
                    $this->_childServices[$childServiceName] = Yii::createObject($service);
                } else {
                    throw new InvalidConfigException('Child Service [' . $childServiceName . '] is disabled in ' . get_called_class() . ', you must enable it! ');
                }
            } else {
                throw new InvalidConfigException('Child Service [' . $childServiceName . '] does not exist in ' . get_called_class() . ', you must config it! ');
            }
        }

        return $this->_childServices[$childServiceName];
    }


    public function __get ($childServiceName)
    {
        return $this->getChildService($childServiceName);
    }

    /**
     * 通过 actionXxxx() 调用服务，  可以在全局services中继承__call(),用来做服务调用日志记录
     * @param string $originMethod
     * @param array $arguments
     *
     * @return mixed
     */
    public function __call ($originMethod, $arguments)
    {
        if (isset($this->_callFunc[$originMethod])) {
            $method = $this->_callFunc[$originMethod];
        } else {
            $method = 'action'.ucfirst($originMethod);
            $this->_callFunc[$originMethod] = $method;
        }
        if (method_exists($this, $method)) {
            $return = call_user_func_array([$this, $method], $arguments);
            return $return;
        } else {
            throw new InvalidCallException('service method is not exist.  '.get_class($this)."::$method");
        }
    }
}
