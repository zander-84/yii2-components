<?php
/**
 * Created by PhpStorm.
 * User: M
 * Date: 2017/9/14
 * Time: 下午9:06
 */

namespace zander84\helpers\bootstrap;

use yii\base\BootstrapInterface;

class LanguageSelector implements BootstrapInterface{


    public $supportLanguage;

    public function bootstrap($app)
    {
        $preferredLanguage = $app->request->getPreferredLanguage($this->supportLanguage);
        $app->language = $preferredLanguage;
    }
}
