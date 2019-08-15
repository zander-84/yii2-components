<?php
/**
 * Created by PhpStorm.
 * User: marvin
 * Date: 2018/7/29
 * Time: 09:21
 */

namespace zander84\helpers\rewrite;


use yii\helpers\VarDumper;
use yii\log\Logger;
use Yii;

class DbTarget extends \yii\log\DbTarget
{
    public $application = true;
    public $queue = false;
    public $queueName = false;
    public $queueClass = false;
    public $jsonEncode = false;


    //public function collect($messages, $final)
    //{
    //    $this->messages = array_merge($this->messages, static::filterMessages($messages, $this->getLevels(), $this->categories, $this->except));
    //    $count = count($this->messages);
    //    if ($count > 0 && ($final || $this->exportInterval > 0 && $count >= $this->exportInterval)) {
    //
    //        //if ($this->application && ($context = $this->getContextMessage()) !== '') {
    //        //    $this->messages[] = [$context, Logger::LEVEL_INFO, 'application', YII_BEGIN_TIME];
    //        //}
    //        // set exportInterval to 0 to avoid triggering export again while exporting
    //        $oldExportInterval = $this->exportInterval;
    //        $this->exportInterval = 0;
    //        $this->export();
    //        $this->exportInterval = $oldExportInterval;
    //
    //        $this->messages = [];
    //    }
    //    if ($this->application){
    //        $this->messages[] = [$this->getContextMessage(), Logger::LEVEL_INFO, 'application', YII_BEGIN_TIME];
    //    }
    //}



    public function export()
    {

        if($this->queue){
            $queue_name = $this->queueName;
            foreach ($this->messages as $message) {
                list($text, $level, $category, $timestamp) = $message;
                if (!is_string($text)) {
                    // exceptions may not be serializable if in the call stack somewhere is a Closure
                    if ($text instanceof \Throwable || $text instanceof \Exception) {
                        $text = (string) $text;
                    } else {
                        $text = VarDumper::export($text);
                    }
                }
                $data = [
                    //'user_id' => Yii::$app->user->isGuest ? -1 : Yii::$app->user->identity->getId(),
                    //'action'=> Yii::$app->controller->id.'/'.Yii::$app->controller->action->id,
                    'level'=>$level,
                    'category'=>$category,
                    'log_time'=>$timestamp,
                    'prefix'=>$this->getMessagePrefix($message),
                    //'method' => Yii::$app->request->method,
                    //'ip' => ip2long(Yii::$app->request->userIP),
                    //'get_msg' => json_encode($_GET),
                    //'post_msg' => json_encode($_POST),
                    //'server_msg' => json_encode($_SERVER),
                    //'insert_data' => date('Y-m-d H:i:s'),
                    //'update_data' => date('Y-m-d H:i:s'),
                    'message'=>$text,
                ];

                if($this->jsonEncode){
                    $data = json_encode($data);
                }
                Yii::$app->$queue_name->push(new $this->queueClass([
                    'data' => $data,
                ]));
            }
        }else{
            parent::export();
        }

    }
}
