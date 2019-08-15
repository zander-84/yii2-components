<?php
/**
 * Created by PhpStorm.
 * User: marvin
 * Date: 2018/7/29
 * Time: 09:21
 */

namespace zander84\helpers\rewrite;


use Interop\Amqp\AmqpMessage;
use yii\db\Transaction;
use yii\queue\amqp_interop\Queue;

class Rabbitmq extends Queue
{
    public function pushContent($data)
    {
        $this->open();
        $this->setupBroker();
        $topic = $this->context->createTopic($this->exchangeName);
        $message = $this->context->createMessage($data);
        $message->setDeliveryMode(AmqpMessage::DELIVERY_MODE_PERSISTENT);
        $message->setMessageId(uniqid('', true));
        $message->setTimestamp(time());
        $message->setProperty(self::ATTEMPT, 1);
        $message->setProperty(self::TTR, $this->ttr);
        $producer = $this->context->createProducer();
        $producer->send($topic, $message);

    }
}
