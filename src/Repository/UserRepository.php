<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function getUsersPaginated( $client,int $page, int $limit = 5): array
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder()
                        ->select('c', 'u')
                        ->from('App\Entity\User', 'u')
                        ->join('u.client','c')
                        ->andWhere('c.id = :id')
                        ->setParameter('id', $client->getId())
                        ->setFirstResult(($page - 1) * $limit)
                        ->setMaxResults($limit);

        $usersPaginated = $queryBuilder->getQuery()->getResult();

        $queryBuilder = $this->createQueryBuilder('u')
                        ->select('count(u.id)');

        $pages = ceil(($queryBuilder->getQuery()->getResult()[0][1])/$limit);

        return ['users' => $usersPaginated,
                'pages' => $pages,
            ];
    }
}
