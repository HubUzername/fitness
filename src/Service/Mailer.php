<?php
    namespace App\Service;

    use App\Entity\User;
    use \Swift_Mailer;
    use \Swift_Message;
    use \Twig_Environment;
    use App\Entity\Lessons;
    
    class Mailer {
        public const FROM_ADDRESS = 'aleksey@mtee.ru';
    
        /**
         * @var Swift_Mailer
         */
        private $mailer;
    
        /**
         * @var Twig_Environment
         */
        private $twig;
    
        public function __construct(Swift_Mailer $mailer, Twig_Environment $twig)  {
            $this->mailer = $mailer;
            $this->twig = $twig;
        }

        public function sendWorkoutMessage(User $user, Lessons $lesson) {
            $messageBody = $this->twig->render('mail/workoutNotify.html.twig', [ 'username' => $user->getNameLastName(), 'workout' => $lesson->getName() ]);
            $message = new Swift_Message();
            $message
                ->setSubject('Скоро начнутся занятия!')
                ->setFrom(self::FROM_ADDRESS)
                ->setTo($user->getEmail())
                ->setBody($messageBody, 'text/html');

            $this->mailer->send($message);
        }

        public function sendConfirmationMessage(User $user) {
            $messageBody = $this->twig->render('mail/confirmation.html.twig', [ 'hash' => $user->getActivationHash(), 'username' => $user->getNameLastName() ]);
    
            $message = new Swift_Message();
            $message
                ->setSubject('Вы успешно прошли регистрацию!')
                ->setFrom(self::FROM_ADDRESS)
                ->setTo($user->getEmail())
                ->setBody($messageBody, 'text/html');
    
            $this->mailer->send($message);
        }

        public function sendTestMessage() {
            $messageBody = $this->twig->render('mail/confirmation.html.twig', [ 'hash' => "123", 'username' => "Test Message" ]);

            $message = new Swift_Message();
            $message
                ->setSubject('Вы успешно прошли регистрацию!')
                ->setFrom(self::FROM_ADDRESS)
                ->setTo("a.baissarov@gmail.com")
                ->setBody($messageBody, 'text/html');

            $this->mailer->send($message);
        }
    }