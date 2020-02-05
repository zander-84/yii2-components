<?php
namespace zander84\helpers\services;

/**
 * Class Activity
 *
 * @property \zander84\helpers\services\sales $sales
 * @method data($data)  获取数据
 */
class Activity extends \zander84\helpers\app\Services
{
    public function list(){
        var_dump($this->childServices);
        var_dump($this->_childServices);
    }

    public function actionData($data){
        var_dump($data);
    }

}
