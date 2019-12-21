<?php

namespace zander84\helpers\models;

use Yii;
use yii\base\Model;


class BaseForm extends Model
{
    public $real_model;
    public $top_errors;


    //异常存在于数据库down或者事务没有激活，不使用savepoint事务
    //______________________________________________________________________
    public static function transaction (\Closure $callback, $attempts = 1, $isolationLevel = null)
    {


        for ($currentAttempt = 1; $currentAttempt <= $attempts; $currentAttempt++) {

            //____ 开启事务
            try {
                $transaction = Yii::$app->db->beginTransaction($isolationLevel);
            } catch (\Throwable $e) {
                Yii::$app->db->close();

                if ($currentAttempt >= $attempts) {
                    Yii::error('beginTransaction 失败');
                    return false;
                } else {
                    continue;
                }
            }

            //____ 执行sql
            $boolean = $callback();

            //____ 提交事务
            if ($boolean) {

                try {
                    $transaction->commit();
                    return true;
                } catch (\Throwable $e) {
                    Yii::$app->db->close();
                    if ($currentAttempt >= $attempts) {
                        Yii::error('commit 失败');
                        return false;
                    } else {
                        continue;
                    }
                }
            } else {

                try {
                    $transaction->rollBack();
                } catch (\Throwable $e) {
                    Yii::$app->db->close();
                    Yii::error('rollBack 失败');
                }

                return false;
            }
        }

    }


}
