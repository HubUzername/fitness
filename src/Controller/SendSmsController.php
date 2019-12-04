<?php
    namespace App\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\Routing\Annotation\Route;
    use Symfony\Component\HttpFoundation\Response;

    class SendSmsController extends AbstractController {
        /**
         * @Route("/api/send", name="send.sms")
         */
        public function sendSms() {
            $responseArray = [];
            $response = new Response();
            if(!isset($_GET['phone']) || preg_match('/^([0-9]{10,20})$/', $_GET['phone']) && isset($_GET['phone'])) {
                $response->setStatusCode(Response::HTTP_BAD_REQUEST);
                $responseArray = ["status" => false, "error" => "Не указан, или неверно указан, GET параметр номера телефона (phone)"];
            }
            else if(!isset($_GET['message'])) {
                $response->setStatusCode(Response::HTTP_BAD_REQUEST);
                $responseArray = ["status" => false, "error" => "Не указан, или неверно указан, GET параметр сообщения (message)"];
            }
            else {
                // Отправляем SMS c вероятностью 50%
                if(rand(0, 1) == 1) {
                    $response->setStatusCode(Response::HTTP_OK);
                    $responseArray = ["status" => true, "error" => "Сообщение успешно отправлено"];
                }
                else {
                    $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
                    $responseArray = ["status" => false, "error" => "Не удалось отправить сообщение, попытка будет повторена через 10 минут"];
                }
            }
            $response->setContent(json_encode($responseArray));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
    }
