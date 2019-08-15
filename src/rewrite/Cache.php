<?php

namespace zander84\helpers\rewrite;
use yii\helpers\StringHelper;



class Cache extends \yii\redis\Cache
{
    public function buildKey($key)
    {
        if (is_string($key)) {
            $key = StringHelper::byteLength($key) <= 100 ? $key : md5($key);
        } else {
            $key = md5(json_encode($key));
        }

        return $this->keyPrefix . $key;
    }
}
