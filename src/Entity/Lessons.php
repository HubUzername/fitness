<?php
    namespace App\Entity;

    use App\Entity\User;
    use Doctrine\ORM\Mapping as ORM;
    use Doctrine\Common\Collections\ArrayCollection;
    use App\Entity\Subscription;

    /**
     * @ORM\Entity(repositoryClass="App\Repository\LessonsRepository")
     */
    class Lessons {
        /**
         * @ORM\Id()
         * @ORM\GeneratedValue()
         * @ORM\Column(type="integer")
         */
        private $id;

        /**
         * @var string
         * @ORM\Column(type="string", length=64)
         */
        private $name;

        /**
         * @var string
         * @ORM\Column(type="string", length=64)
         */
        private $trainerName;

        /**
         * @var string
         * @ORM\Column(type="text")
         */
        private $description;

        /**
         * @var Subscription[]
         * @ORM\OneToMany(targetEntity=Subscription::class, mappedBy="lessons")
         */
        private $subscribers;

        /**
         * @var int
         */
        private $tempSubscribers;

        public function __construct() {
            $this->subscribers = new ArrayCollection();
        }

        /**
         * @return \App\Entity\Subscription
         */
        public function getSubscriptions(): \App\Entity\Subscription {
            return $this->subscriptions;
        }

        public function getId(): ?int {
            return $this->id;
        }

        /**
         * @return string|null
         */
        public function getName(): ?string {
            return $this->name;
        }

        /**
         * @return int
         */
        public function getTempSubscribers(): int {
            return $this->tempSubscribers;
        }

        /**
         * @param int $tempSubscribers
         */
        public function setTempSubscribers(int $tempSubscribers): void {
            $this->tempSubscribers = $tempSubscribers;
        }


        /**
         * @param string $name
         */
        public function setName(string $name): void {
            $this->name = $name;
        }

        /**
         * @return string|null
         */
        public function getTrainerName(): ?string  {
            return $this->trainerName;
        }

        /**
         * @param string $trainerName
         */
        public function setTrainerName(string $trainerName): void {
            $this->trainerName = $trainerName;
        }

        /**
         * @return string|null
         */
        public function getDescription(): ?string {
            return $this->description;
        }

        /**
         * @param string $description
         */
        public function setDescription(string $description): void {
            $this->description = $description;
        }
    }
