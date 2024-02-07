<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 *
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

        public function getCategoriesByFilters($options = [])
        {
            $query = $this->createQueryBuilder('c');

            if (isset($options['name']) && $options['name']) {
                $query->andWhere('c.name is null');
            }
            if (isset($options['company']) && $options['company']) {
                $query->andWhere('c.company IN (:company)')
                    ->setParameter('company', $options['company']);
            }

             return $query->getQuery()->getResult();
        }
}
