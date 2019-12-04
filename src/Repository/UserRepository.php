<?php

    namespace App\Repository;

    use App\Entity\User;
    use App\Entity\Lessons;
    use App\Entity\Subscription;
    use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
    use Doctrine\Common\Persistence\ManagerRegistry;
    use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
    use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
    use Symfony\Component\Security\Core\User\UserInterface;

    /**
     * @method User|null find($id, $lockMode = null, $lockVersion = null)
     * @method User|null findOneBy(array $criteria, array $orderBy = null)
     * @method User[]    findAll()
     * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
     */
    class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface {
        public function __construct(ManagerRegistry $registry) {
            parent::__construct($registry, User::class);
        }


        public function existEmail(string $email) {
            $data = $this->findOneBy(["email" => $email]);
            if($data != null) throw new \ErrorException("Такой E-mail уже зарегистрирован");
            else return $data;
        }

        /**
         * @param int $userId
         * @return User|null
         */
        public function getUser(int $userId): ?User {
            return $this->findOneBy(["id" => $userId]);
        }

        public function upgradePassword(UserInterface $user, string $newEncodedPassword): void {
            if (!$user instanceof User) {
                throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
            }

            $user->setPassword($newEncodedPassword);
            $this->_em->persist($user);
            $this->_em->flush();
        }
    }
