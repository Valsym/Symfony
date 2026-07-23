<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\Cache\Adapter\RedisAdapter;

/**
 * @extends ServiceEntityRepository<Product>
 */
use Symfony\Contracts\Cache\CacheInterface;

class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private CacheInterface $cache)
    {
        parent::__construct($registry, Product::class);

        // Или инициализировать здесь
//        $this->cache = new RedisAdapter(
//            RedisAdapter::createConnection('redis://localhost:6379'),
//            'products_'
//        );
    }

    public function findProductsByKey(string $key)
    {
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder->where('p.name LIKE :key')
            ->setParameter('key', "{%$key%}" );

        return $queryBuilder->getQuery()->getResult();
    }

    public function search(?string $name, ?float $minPrice, ?float $maxPrice, ?Category $category): QueryBuilder
    {
        $qb = $this->createQueryBuilder('p');

        if ($name) {
            $qb->andWhere('p.name LIKE :name')
               ->setParameter('name', '%' . $name . '%');
        }

        if ($minPrice) {
            $qb->andWhere('p.price >= :minPrice')
               ->setParameter('minPrice', $minPrice);
        }

        if ($maxPrice) {
            $qb->andWhere('p.price <= :maxPrice')
               ->setParameter('maxPrice', $maxPrice);
        }

        if ($category) {
            $qb->andWhere('p.category = :category')
                ->setParameter('category', $category);
        }
        // Сортировка по ID по умолчанию
        $qb->orderBy('p.id', 'ASC');

        return $qb;
    }

    public function findCachedProducts(): array
    {
        return $this->cache->get('latest_products', function (ItemInterface $item) {
            $item->expiresAfter(7200); // 2 часа

            return $this->createQueryBuilder('p')
                ->orderBy('p.publishedAt', 'DESC')
                ->setMaxResults(10)
                ->getQuery()
                ->getResult();
        });
    }


    //    /**
    //     * @return Product[] Returns an array of Product objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Product
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
