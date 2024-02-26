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

    public function getCategoriesByFilters($company = null, $options = [])
    {
        $query = $this->createQueryBuilder('c');

        if ($company) {
            $query->andWhere('c.company = :company')
                ->setParameter('company', $company);
        }

        if (isset($options['alias']) && $options['alias'] !== '') {
            $parts = explode(' ', $options['alias']);
            $subAnd = [];
            foreach ($parts as $k => $p) {
                $tag = 'alias_' . $k;
                $subOr = [];
                foreach (['c.name'] as $f) {
                    $subOr[] = "{$f} LIKE :{$tag}";
                }
                $subAnd[] = '(' . implode(' OR ', $subOr) . ')';
                $query->setParameter($tag, "%$p%");
            }
            $query
                ->andWhere('(' . implode(' AND ', $subAnd) . ')');
        }

        return $query->getQuery()->getResult();
    }
}
