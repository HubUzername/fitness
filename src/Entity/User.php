<?php
    namespace App\Entity;

    use App\Entity\Subscription;
    use Doctrine\ORM\Mapping as ORM;
    use Symfony\Component\Security\Core\User\UserInterface;
    use Doctrine\Common\Collections\ArrayCollection;

    /**
     * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
     */
    class User implements UserInterface {
        const ACCOUNT_INACTIVE = 0;
        const ACCOUNT_ACTIVE = 1;
        const ACCOUNT_BANNED = 2;

        const SEX_MALE = 1;
        const SEX_FEMALE = 2;

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
        private $nameLastName;

        /**
         * @var \DateTime
         * @ORM\Column(type="date", nullable=false)
         */
        private $birthday;

        /**
         * @var string
         * @ORM\Column(type="string", length=100, nullable=true)
         */
        private $activationHash;

        /**
         * @var integer
         * @ORM\Column(type="smallint", length=1)
         */
        private $sex;

        /**
         * @var Subscription
         * @ORM\OneToMany(targetEntity="App\Entity\Subscription", mappedBy="user")
         */
        private $subscriptions;

        /**
         * @var string
         * @ORM\Column(type="integer", length=20)
         */
        private $phone;

        /**
         * @var string
         */
        private $updatedPhoto;

        /**
         * @var string|null
         * @ORM\Column(type="string", length=255, nullable=true)
         */
        private $photo;

        /**
         * @var string
         * @ORM\Column(type="smallint", length=1)
         */
        private $status;

        /**
         * @ORM\Column(type="string", length=180, unique=true)
         */
        private $email;

        /**
         * @ORM\Column(type="json")
         */
        private $roles = [];

        /**
         * @var string The hashed password
         * @ORM\Column(type="string")
         */
        private $password;

        /**
         * @var Subscription[]
         * @ORM\OneToMany(targetEntity=Subscription::class, mappedBy="user")
         */
        private $subscribers;

        public function __construct() {
            $this->subscribers = new ArrayCollection();
        }

        /**
         * @return \App\Entity\Subscription
         */
        public function getSubscriptions(): \App\Entity\Subscription {
            return $this->subscriptions;
        }

        /**
         * @param \App\Entity\Subscription $subscriptions
         */
        public function setSubscriptions(\App\Entity\Subscription $subscriptions): void {
            $this->subscriptions = $subscriptions;
        }

        public function randomPassword(int $len = 8) {
            $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
            $pass = [];
            $alphaLength = strlen($alphabet) - 1;
            for ($i = 0; $i < $len; $i++) {
                $n = rand(0, $alphaLength);
                $pass[] = $alphabet[$n];
            }
            return implode($pass);
        }

        /**
         * @return string|null
         */
        public function getActivationHash(): ?string {
            return $this->activationHash;
        }

        /**
         * @param string $activationHash
         */
        public function setActivationHash(string $activationHash): void {
            $this->activationHash = $activationHash;
        }

        /**
         * @return string|null
         */
        public function getUpdatedPhoto(): ?string {
            return $this->updatedPhoto;
        }

        /**
         * @param string $updatedPhoto
         */
        public function setUpdatedPhoto(string $updatedPhoto): void {
            $this->updatedPhoto = $updatedPhoto;
        }



        public function getId(): ?int {
            return $this->id;
        }

        public function getEmail(): ?string {
            return $this->email;
        }

        public function setEmail(string $email): self {
            $this->email = $email;

            return $this;
        }

        /**
         * A visual identifier that represents this user.
         *
         * @see UserInterface
         */
        public function getUsername(): string {
            return (string) $this->email;
        }

        /**
         * @see UserInterface
         */
        public function getRoles(): array {
            $roles = $this->roles;
            // guarantee every user at least has ROLE_USER
            $roles[] = 'ROLE_USER';
            return array_unique($roles);
        }

        public function setRoles(array $roles): self {
            $this->roles = $roles;
            return $this;
        }

        /**
         * @see UserInterface
         */
        public function getPassword(): string  {
            return (string) $this->password;
        }

        public function setPassword(string $password): self {
            $this->password = $password;
            return $this;
        }

        /**
         * @see UserInterface
         */
        public function getSalt() {
            // not needed when using the "bcrypt" algorithm in security.yaml
        }

        /**
         * @see UserInterface
         */
        public function eraseCredentials() {
            $this->plainPassword = null;
        }

        /**
         * @return string|null
         */
        public function getNameLastName(): ?string {
            return $this->nameLastName;
        }

        /**
         * @param string $nameLastName
         */
        public function setNameLastName(string $nameLastName): void {
            $this->nameLastName = $nameLastName;
        }

        /**
         * @return \DateTime|null
         */
        public function getBirthday(): ?\DateTime {
            return $this->birthday;
        }

        /**
         * @param \DateTime $birthday
         */
        public function setBirthday(\DateTime $birthday): void {
            $this->birthday = $birthday;
        }

        /**
         * @return int|null
         */
        public function getSex(): ?int {
            return $this->sex;
        }

        /**
         * @param int $sex
         */
        public function setSex(int $sex): void {
            $this->sex = $sex;
        }

        /**
         * @return string|null
         */
        public function getPhone(): ?string
        {
            return $this->phone;
        }

        /**
         * @param string $phone
         */
        public function setPhone(string $phone): void {
            $this->phone = $phone;
        }

        /**
         * @return string|null
         */
        public function getPhoto(): ?string {
            return $this->photo;
        }

        /**
         * @param string $photo
         * @return User
         */
        public function setPhoto(string $photo): ?User {
            $this->photo = $photo;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getStatus(): ?string {
            return $this->status;
        }

        /**
         * @param string $status
         */
        public function setStatus(string $status): void {
            $this->status = $status;
        }
    }
