<?php
    namespace App\EventListener;
    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
    use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
    use Symfony\Component\HttpKernel\KernelEvents;
    use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
    use App\Entity\User;
    use Symfony\Component\HttpFoundation\RedirectResponse;
    use Symfony\Component\Routing\RouterInterface;

    class RequestListener implements EventSubscriberInterface {
        /**
         * @var TokenStorageInterface $tokenStorage
         */
        private $tokenStorage;
        private $router;

        public function __construct(TokenStorageInterface $tokenStorage, RouterInterface $router) {
            $this->tokenStorage = $tokenStorage;
            $this->router = $router;
        }
        public function onKernelResponse(FilterResponseEvent $event) {
            $token = $this->tokenStorage->getToken();
            if($token != null) {
                $reflect = new \ReflectionClass($token);
                if ($reflect->getShortName() == "PostAuthenticationGuardToken") {
                    $user = $token->getUser();
                    if($user->getStatus() != User::ACCOUNT_ACTIVE) {
                        $event->setResponse(new RedirectResponse($this->router->generate('app_logout')));
                    }
                }
            }
        }

        public static function getSubscribedEvents() {
            return [ KernelEvents::RESPONSE => [['onKernelResponse', 255]] ];
        }
    }