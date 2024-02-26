<?php

namespace App\Repository;

use App\Entity\Company;
use App\Entity\Invoice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Invoice>
 *
 * @method Invoice|null find($id, $lockMode = null, $lockVersion = null)
 * @method Invoice|null findOneBy(array $criteria, array $orderBy = null)
 * @method Invoice[]    findAll()
 * @method Invoice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InvoiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invoice::class);
    }

    public function getInvoicesByFilters($company = null, $options = [])
    {
        $query = $this->createQueryBuilder('i');

        if ($company) {
            $query->andWhere('i.company = :company')
                ->setParameter('company', $company);
        }
        if (isset($options['discountTotal']) && $options['discountTotal']) {
            $query
                ->leftJoin('i.items', 'items')
                ->leftJoin('items.product', 'product')
                ->andWhere('(SELECT SUM(product.price * i2.quantity * (1 - i2.taxes / 100)) FROM App\Entity\Item i2) = :discountTotal')
                ->setParameter('discountTotal', $options['discountTotal']);
        }
        if (isset($options['discountAmount']) && $options['discountAmount']) {
            $query
                ->andWhere('i.discountAmount LIKE :discountAmount')
                ->setParameter('discountAmount', '%' . $options['discountAmount'] . '%');
        }
        if (isset($options['discountPercent']) && $options['discountPercent']) {
            $query
                ->andWhere('i.discountPercent LIKE :discountPercent')
                ->setParameter('discountPercent', '%' . $options['discountPercent'] . '%');
        }
        if (isset($options['status']) && $options['status']) {
            $query
                ->andWhere('i.status LIKE :status')
                ->setParameter('status', '%' . $options['status']->name . '%');
        }

        return $query->getQuery()->getResult();
    }

    public function findByDateRange(Company $company, \DateTime $startDate, \DateTime $endDate)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.company = :company')
            ->andWhere('i.createdAt >= :startDate')
            ->andWhere('i.createdAt <= :endDate')
            ->setParameter('company', $company)
            ->setParameter('startDate', $startDate->format('Y-m-d'))
            ->setParameter('endDate', $endDate->format('Y-m-d'))
            ->orderBy('i.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    //    /**
    //     * @return Invoice[] Returns an array of Invoice objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('i.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Invoice
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
