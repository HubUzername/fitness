<?php
    namespace App\Repository;

    use App\Entity\Subscription;
    use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
    use Doctrine\Common\Persistence\ManagerRegistry;
    use App\Entity\Lessons;
    use App\Entity\User;

    /**
     * @method Subscription|null find($id, $lockMode = null, $lockVersion = null)
     * @method Subscription|null findOneBy(array $criteria, array $orderBy = null)
     * @method Subscription[]    findAll()
     * @method Subscription[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
     */
    class SubscriptionRepository extends ServiceEntityRepository {
        public function __construct(ManagerRegistry $registry) {
            parent::__construct($registry, Subscription::class);
        }

        public function checkSubscription(User $user, int $lessonId) {
            return $this->findOneBy(["lesson" => $lessonId, "user" => $user]);
        }

        public function getNotSubscribed(array $subscriptions, array $lessons): array {
            $subArray = [];
            $returnArray = [];
            foreach($subscriptions as $sub) $subArray[] = $sub->getLesson()->getId();
            foreach($lessons as $lesson) {
                if(array_search($lesson->getId(), $subArray) === false) {
                    $returnArray[] = $lesson;
                }
            }
            return $returnArray;
        }
    }
