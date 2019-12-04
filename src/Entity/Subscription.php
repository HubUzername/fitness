<?php
    namespace App\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use App\Entity\Lessons;
    use App\Entity\User;

    /**
     * @ORM\Entity(repositoryClass="App\Repository\SubscriptionRepository")
     */
    class Subscription {
        const NOTIFIER_NONE = 0;
        const NOTIFIER_BY_EMAIL = 1;
        const NOTIFIER_BY_PHONE = 2;
        /**
         * @ORM\Id()
         * @ORM\GeneratedValue()
         * @ORM\Column(type="integer")
         */
        private $id;

        /**
         * @var Lessons
         * @ORM\ManyToOne(targetEntity="App\Entity\Lessons", inversedBy="subscribers")
         * @ORM\JoinColumn(referencedColumnName="id")
         */
        private $lesson;

        /**
         * @var int
         * @ORM\Column(type="smallint", length=1, nullable=false)
         */
        private $notifierBy;

        /**
         * @var User
         * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="subscribers")
         * @ORM\JoinColumn(referencedColumnName="id")
         */
        private $user;

        public function getId(): ?int {
            return $this->id;
        }

        /**
         * @return \App\Entity\Lessons
         */
        public function getLesson(): \App\Entity\Lessons  {
            return $this->lesson;
        }

        /**
         * @param \App\Entity\Lessons $lesson
         */
        public function setLesson(\App\Entity\Lessons $lesson): void {
            $this->lesson = $lesson;
        }

        /**
         * @return int
         */
        public function getNotifierBy(): int {
            return $this->notifierBy;
        }

        /**
         * @param int $notifierBy
         */
        public function setNotifierBy(int $notifierBy): void {
            $this->notifierBy = $notifierBy;
        }

        /**
         * @return \App\Entity\User
         */
        public function getUser(): \App\Entity\User {
            return $this->user;
        }

        /**
         * @param \App\Entity\User $user
         */
        public function setUser(\App\Entity\User $user): void {
            $this->user = $user;
        }


    }
