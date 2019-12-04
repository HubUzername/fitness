<?php

    namespace App\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\Routing\Annotation\Route;
    use App\Repository\UserRepository;
    use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
    use Symfony\Component\Form\Extension\Core\Type\SubmitType;
    use Symfony\Component\Form\Extension\Core\Type\PasswordType;
    use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
    use Symfony\Component\HttpFoundation\Request;
    use App\Entity\User;
    use App\Entity\Lessons;
    use App\Entity\Subscription;

    class UserController extends AbstractController {
        /**
         * @var UserRepository $userRepository
         */
        public $userRepository;

        public function __construct(UserRepository $userRepository) {
            $this->userRepository = $userRepository;
        }

        /**
         * @Route("/profile/lessons/manage/{subId}/{status}", name="user.lesson.status", requirements={"subId"="\d{1,5}", "status"="0|1|2"})
         */
        public function manageLessonNotification(int $subId, int $status) {
            $em = $this->getDoctrine()->getRepository(Subscription::class);
            $subscription = $em->findOneBy(["id" => $subId]);
            if($subscription !== null) {
                if($status == 0) $subscription->setNotifierBy(Subscription::NOTIFIER_NONE);
                else if($status == 1) $subscription->setNotifierBy(Subscription::NOTIFIER_BY_EMAIL);
                else $subscription->setNotifierBy(Subscription::NOTIFIER_BY_PHONE);
                $em = $this->getDoctrine()->getManager();
                $em->flush();
                $this->addFlash("success", "Способ оповещения о начале занятий успешно изменен");
            }
            return $this->redirectToRoute('user.subscriptions');
        }

        /**
         * @Route("/profile/lessons/subscribe/{lessonId}", name="user.subscribe", requirements={"lessonId"="\d{1,5}"})
         */
        public function subscribeUser(int $lessonId) {
            $em = $this->getDoctrine()->getRepository(Lessons::class);
            $lesson = $em->findOneBy(["id" => $lessonId]);
            if($lesson !== null) {
                $subEm = $this->getDoctrine()->getRepository(Subscription::class);
                $existSub = $subEm->checkSubscription($this->getUser(), $lessonId);
                if($existSub === null) {
                    $subscription = new Subscription();
                    $subscription->setLesson($lesson);
                    $subscription->setUser($this->getUser());
                    $subscription->setNotifierBy(Subscription::NOTIFIER_NONE);
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($subscription);
                    $em->flush();
                    $this->addFlash("success", "Вы подписаны на занятие, и теперь вы будете получать оповещение о начале занятий");
                }
                else {
                    $em = $this->getDoctrine()->getManager();
                    $em->remove($existSub);
                    $em->flush();
                    $this->addFlash("success", "Подписка на занятие отменена");
                }
            }
            return $this->redirectToRoute('user.subscriptions');
        }

        /**
         * @Route("/profile/lessons", name="user.subscriptions")
         */
        public function userLessons() {
            $em = $this->getDoctrine()->getRepository(Lessons::class);
            $lessons = $em->findAll();
            $em = $this->getDoctrine()->getRepository(Subscription::class);
            $subscriptions = $em->findBy(["user" => $this->getUser()->getId()]);
            $notSubscribed = $em->getNotSubscribed($subscriptions, $lessons);
            return $this->render('user/subscriptions.html.twig', ["lessons" => $notSubscribed, "subscriptions" => $subscriptions]);
        }

        /**
         * @Route("/profile/changepassword", name="user.changepassword")
         */
        public function userChangepassword(Request $request) {
            $user = $this->getUser();
            $form = $this->createFormBuilder($user)
                ->setMethod('POST')
                ->add("password", RepeatedType::class, ["required" => true, 'type' => PasswordType::class, 'invalid_message' => 'Указанные пароли должны совпадать'])
                ->add("save", SubmitType::class)
                ->getForm();

            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()) {
                $user->setStatus(User::ACCOUNT_ACTIVE);
                $em = $this->getDoctrine()->getManager();
                $em->flush();
                $this->addFlash("success", "Ваш пароль успешно изменен. Не забудьте его!");
                return $this->redirectToRoute('user.profile');
            }
            return $this->render('user/changepass.html.twig', [
                'form' => $form->createView()
            ]);
        }

        /**
         * @Route("/profile", name="user.profile")
         */
        public function userProfile(Request $request) {
            $this->getUser()->getSex() == User::SEX_MALE ? $sex = "Мужской" : $sex = "Женский";
            return $this->render('user/profile.html.twig', ["sex" => $sex]);
        }

        /**
         * @Route("/activation/{hash}", name="user.activation")
         */
        public function userActivation(string $hash, Request $request) {
            if($this->getUser()) return $this->redirectToRoute('home');
            $user = $this->userRepository->findOneBy(["activationHash" => $hash]);
            if($user == null) return $this->redirectToRoute('home');
            else {
                if($user->getStatus() != User::ACCOUNT_INACTIVE) return $this->redirectToRoute('home');
                $error = "";
                $form = $this->createFormBuilder($user)
                    ->setMethod('POST')
                    ->add("password", RepeatedType::class, ["required" => true, 'type' => PasswordType::class, 'invalid_message' => 'Указанные пароли должны совпадать'])
                    ->add("save", SubmitType::class)
                    ->getForm();

                $form->handleRequest($request);
                if($form->isSubmitted() && $form->isValid()) {
                    $user->setStatus(User::ACCOUNT_ACTIVE);
                    $em = $this->getDoctrine()->getManager();
                    $em->flush();
                    return $this->redirectToRoute('user.profile');
                }
                return $this->render('user/activation.html.twig', [
                    'error' => $error,
                    'form' => $form->createView()
                ]);
            }
        }
    }
