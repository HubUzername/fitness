<?php
    namespace App\Consumer;

    use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
    use PhpAmqpLib\Message\AMQPMessage;
    use Symfony\Component\HttpClient\HttpClient;

    class SmsConsumer implements ConsumerInterface {
        /**
         * @var \OldSound\RabbitMqBundle\RabbitMq\Producer
         */
        private $smsDelayProducer;
        public function __construct(\OldSound\RabbitMqBundle\RabbitMq\Producer $smsDelayProducer) {
            $this->smsDelayProducer = $smsDelayProducer;
        }

        public function execute(AMQPMessage $msg) {
            echo "\nПришло сообщение ".$msg->body."\n";
            $body = json_decode($msg->body, true);
            if(isset($body['phone'])) {
                $client = HttpClient::create();
                $response = $client->request('GET', "https://mtee.ru/api/send?phone={$body['phone']}&message={$body['text']}");
                $statusCode = $response->getStatusCode();
                if($statusCode == 200) {
                    echo "Отправлено СМС на номер {$body['phone']} с текстом: {$body['text']}\n";
                    return ConsumerInterface::MSG_ACK;
                }
                else {
                    echo "Кладем в очередь send.delayed\n";
                    $this->smsDelayProducer->publish($msg->body, '', [], ["x-delay" => 7000]);
                    return ConsumerInterface::MSG_ACK_SENT;
                }
            }
            return ConsumerInterface::MSG_ACK_SENT;
        }
    }