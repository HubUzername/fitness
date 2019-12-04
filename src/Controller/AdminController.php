<?php
    namespace App\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\Routing\Annotation\Route;
    use Symfony\Component\Form\Extension\Core\Type\TextType;
    use Symfony\Component\Form\Extension\Core\Type\EmailType;
    use Symfony\Component\Form\Extension\Core\Type\SubmitType;
    use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
    use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
    use Symfony\Component\Form\Extension\Core\Type\FileType;
    use Symfony\Component\HttpFoundation\File\Exception\FileException;
    use Symfony\Component\HttpFoundation\File\UploadedFile;
    use App\Form\UserType;
    use Symfony\Component\HttpFoundation\File\File;
    use Symfony\Component\HttpFoundation\Request;
    use App\Entity\User;
    use App\Entity\Lessons;
    use App\Entity\Subscription;
    use App\Service\Mailer;

    class AdminController extends AbstractController {
        /**
         * @var \OldSound\RabbitMqBundle\RabbitMq\Producer
         */
        private $smsQueue;

        public function __construct(\OldSound\RabbitMqBundle\RabbitMq\Producer $smsProducer) {
            $this->smsQueue = $smsProducer;
        }

        /**
         * @Route("/admin/lessons/{lessonId}/notification", name="admin.lessons.notify", requirements={"lessonId"="\d{1,5}"})
         */
        public function sendNotification(int $lessonId, Mailer $mailer) {
            $em = $this->getDoctrine()->getRepository(Lessons::class);
            $lesson = $em->findOneBy(["id" => $lessonId]);
            if($lesson != null) {
                $em = $this->getDoctrine()->getRepository(Subscription::class);
                $subscribers = $em->findBy(["lesson" => $lessonId]);
                if($subscribers != null) {
                    foreach($subscribers as $sub) {
                        $user = $sub->getUser();
                        if($user->getStatus() == User::ACCOUNT_BANNED) continue;
                        if($sub->getNotifierBy() == Subscription::NOTIFIER_NONE) continue;
                        if($sub->getNotifierBy() == Subscription::NOTIFIER_BY_EMAIL) {
                            $mailer->sendWorkoutMessage($user, $lesson);
                        }
                        else if($sub->getNotifierBy() == Subscription::NOTIFIER_BY_PHONE) {
                            $smsTemplate = "Здравствуйте, {$user->getNameLastName()}. Через 20 минут начнутся занятия по {$lesson->getName()}";
                            $this->smsQueue->publish(json_encode(["phone" => $user->getPhone(), "text" => $smsTemplate]));
                        }
                    }
                    $this->addFlash("success", "Оповещения о начале занятий по {$lesson->getName()} через 20 минут успешно разосланы.");
                }
                else $this->addFlash("danger", "У данного занятия нет подписчиков. Оповещения не разосланы.");
            }
            else $this->addFlash("danger", "Занятие не найдено");
            return $this->redirectToRoute('admin.lessons');
        }

        /**
         * @Route("/admin/lessons/new", name="admin.lessons.add")
         */
        public function addNewLesson(Request $request) {
            $lesson = new Lessons();
            $form = $this->createFormBuilder($lesson)
                ->setMethod('POST')
                ->add("name", TextType::class, ["required" => true])
                ->add("trainerName", TextType::class, ["required" => true])
                ->add("description", TextType::class, ["required" => true]) // Можно и textarea
                ->add("add", SubmitType::class)
                ->getForm();

            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($lesson);
                $em->flush();
                $this->addFlash("success", "Новое занятие успешно добавлено");
                return $this->redirectToRoute('admin.lessons');
            }
            return $this->render('admin/newlesson.html.twig', ["form" => $form->createView()]);
        }

        /**
         * @Route("/admin/lessons", name="admin.lessons")
         */
        public function lessonsList() {
            $em = $this->getDoctrine()->getRepository(Lessons::class);
            $lessons = $em->findAll();
            $em = $this->getDoctrine()->getRepository(Subscription::class);
            foreach($lessons as $less) {
                $subscriptions = $em->findBy(["lesson" => $less->getId()]);
                $less->setTempSubscribers(count($subscriptions));
            }
            return $this->render('admin/lessons.html.twig', ["lessons" => $lessons]);
        }

        /**
         * @Route("/admin/users", name="admin.users")
         */
        public function showAllUsers() {
            $em = $this->getDoctrine()->getRepository(User::class);
            $users = $em->findAll();
            return $this->render('admin/allusers.html.twig', ["users" => $users]);
        }

        /**
         * @Route("/admin/users/{userId}/ban", name="admin.users.ban", requirements={"userId"="\d{1,5}"})
         */
        public function banUser(int $userId) {
            $em = $this->getDoctrine()->getRepository(User::class);
            $user = $em->getUser($userId);
            if($user !== null) {
                if($user->getStatus() != User::ACCOUNT_INACTIVE) {
                    if($user->getStatus() == User::ACCOUNT_BANNED) {
                        $user->setStatus(User::ACCOUNT_ACTIVE);
                        $this->addFlash("success", "Клиент разблокирован");
                    }
                    else {
                        $user->setStatus(User::ACCOUNT_BANNED);
                        $this->addFlash("success", "Клиент заблокирован, теперь он не сможет войти в систему и не будет получать оповещения");
                    }
                    $em = $this->getDoctrine()->getManager();
                    $em->flush();
                }
            }
            return $this->redirectToRoute('admin.users.manage', ["userId" => $userId]);
        }

        /**
         * @Route("/admin/users/{userId}/manage", name="admin.users.manage", requirements={"userId"="\d{1,5}"})
         */
        public function manageUser(Request $request, int $userId) {
            $error = "";
            $em = $this->getDoctrine()->getRepository(User::class);
            $user = $em->getUser($userId);
            if($user !== null) {
                $tempEmail = $user->getEmail();
                $form = $this->createForm(UserType::class, $user)->add('add', SubmitType::class);
                $form->handleRequest($request);

                if($form->isSubmitted() && $form->isValid()) {
                    $data = $form->getData();
                    $em = $this->getDoctrine()->getRepository(User::class);
                    try {
                        if($user->getEmail() !== $tempEmail) $em->existEmail($data->getEmail());
                        $em = $this->getDoctrine()->getManager();
                        $em->flush();
                        $uploadedPhoto = $form['updatedPhoto']->getData();
                        if($uploadedPhoto !== null) {
                            $filename = $user->getId() . uniqid() . '.' . $uploadedPhoto->guessExtension();
                            try {
                                unlink($this->getParameter('upload.directory').$user->getPhoto());
                                $uploadedPhoto->move($this->getParameter('upload.directory'), $filename);
                                $user->setPhoto($filename);
                                $em->flush();
                                $this->addFlash("danger", "Новые настройки для клиента успешно заданы");
                            } catch (FileException $e) {
                                $error = $e->getMessage();
                            }
                        }
                        return $this->redirectToRoute('admin.users.manage', ["userId" => $userId]);
                    } catch (\ErrorException $e) {
                        $error = $e->getMessage();
                    }
                }
                return $this->render('admin/edituser.html.twig', [
                    'form' => $form->createView(),
                    'error' => $error,
                    "user" => $user
                ]);
            }
            else return $this->redirectToRoute('admin.users');
        }

        /**
         * @Route("/admin/newuser", name="admin.newuser")
         */
        public function newUser(Request $request, Mailer $mailer) {
            $error = "";
            $user = new User();

            $form = $this->createForm(UserType::class, $user)->add('add', SubmitType::class);
            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();
                $em = $this->getDoctrine()->getRepository(User::class);
                try {
                    if(!$em->existEmail($data->getEmail())) {
                        $tempPassword = $user->randomPassword();
                        $changePasswordUrl = hash("sha256", $user->randomPassword());
                        $user->setPassword($tempPassword);
                        $user->setActivationHash($changePasswordUrl);
                        $user->setStatus(User::ACCOUNT_INACTIVE);
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($user);
                        $em->flush();

                        $uploadedPhoto = $form['updatedPhoto']->getData();
                        if($uploadedPhoto) {
                            $filename = $user->getId().uniqid().'.'.$uploadedPhoto->guessExtension();
                            try {
                                $uploadedPhoto->move("/var/www/mtee/data/www/mtee.ru/tz/public/photos/", $filename);
                                $user->setPhoto($filename);
                                $em->flush();
                                $this->addFlash("danger", "Клиент успешно добавлен, письмо с активацией отправлено на {$user->getUsername()}");
                            } catch (FileException $e) { $error = $e->getMessage(); }
                        }
                        $mailer->sendConfirmationMessage($user);
                        return $this->redirectToRoute('admin.users');
                    }
                } catch(\ErrorException $e) { $error = $e->getMessage(); }
            }

            return $this->render('admin/newuser.html.twig', [
                'form' => $form->createView(),
                'error' => $error
            ]);
        }
    }