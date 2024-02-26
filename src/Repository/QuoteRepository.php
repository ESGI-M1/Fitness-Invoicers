<?php

namespace App\Repository;

use App\Entity\Company;
use App\Entity\Quote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Quote>
 *
 * @method Quote|null find($id, $lockMode = null, $lockVersion = null)
 * @method Quote|null findOneBy(array $criteria, array $orderBy = null)
 * @method Quote[]    findAll()
 * @method Quote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QuoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Quote::class);
    }

    public function getQuotesByFilters($company = null, $options = [])
    {
        $query = $this->createQueryBuilder('q');

        if ($company) {
            $query->andWhere('q.company = :company')
                ->setParameter('company', $company);
        }
        if (isset($options['discountAmount']) && $options['discountAmount']) {
            $query
                ->andWhere('q.discountAmount LIKE :discountAmount')
                ->setParameter('discountAmount', '%' . $options['discountAmount'] . '%');
        }
        if (isset($options['discountPercent']) && $options['discountPercent']) {
            $query
                ->andWhere('q.discountPercent LIKE :discountPercent')
                ->setParameter('discountPercent', '%' . $options['discountPercent'] . '%');
        }
        if (isset($options['status']) && $options['status']) {
            $query
                ->andWhere('q.status LIKE :status')
                ->setParameter('status', '%' . $options['status']->name . '%');
        }

        return $query->getQuery()->getResult();
    }

    public function findByDateRange(Company $company, \DateTime $startDate, \DateTime $endDate)
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.company = :company')
            ->andWhere('q.createdAt >= :startDate')
            ->andWhere('q.createdAt <= :endDate')
            ->setParameter('company', $company)
            ->setParameter('startDate', $startDate->format('Y-m-d'))
            ->setParameter('endDate', $endDate->format('Y-m-d'))
            ->orderBy('q.createdAt', 'desc')
            ->getQuery()
            ->getResult()
        ;
    }

    //    /**
    //     * @return Quote[] Returns an array of Quote objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('q')
    //            ->andWhere('q.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('q.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Quote
    //    {
    //        return $this->createQueryBuilder('q')
    //            ->andWhere('q.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
