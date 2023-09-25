<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 *
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function findAllPaginated(int $page, int $limit = 5): array
    {
        $queryBuilder = $this->createQueryBuilder('p')
                        ->setFirstResult(($page - 1) * $limit)
                        ->setMaxResults($limit);

        $productsPaginated = $queryBuilder->getQuery()->getResult();

        $queryBuilder = $this->createQueryBuilder('p')
                        ->select('count(p.id)');
        $pages = ceil($queryBuilder->getQuery()->getResult()[0][1] / $limit);

        return ['products' => $productsPaginated,
                'pages' => $pages,
            ];
    }
}
