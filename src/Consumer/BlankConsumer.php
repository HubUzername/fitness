<?php
    namespace App\Consumer;

    use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
    use PhpAmqpLib\Message\AMQPMessage;

    class BlankConsumer implements ConsumerInterface {
        /**
         * @var OldSound\RabbitMqBundle\RabbitMq\Producer
         */
        private $smsProducer;
        public function __construct(\OldSound\RabbitMqBundle\RabbitMq\Producer $smsProducer) {
            $this->smsProducer = $smsProducer;
        }

        public function execute(AMQPMessage $msg) {
            echo "Пришло отложенное событие, перенаправляем в основную очередь\n";
            $this->smsProducer->publish($msg->body);
            return self::MSG_ACK;
        }
    }