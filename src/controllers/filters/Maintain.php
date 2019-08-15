<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace zander84\helpers\controllers\filters;

use Yii;
use yii\base\ActionFilter;

class Maintain extends ActionFilter
{

    public function beforeAction($action)
    {
       if(isset(Yii::$app->params['maintaining']) && Yii::$app->params['maintaining']){
           Yii::$app->response->code = Yii::$app->response->codeMaintaining;
           return false;
       }
       return true;
    }
}
