<?php

namespace App\Repository;

use App\Entity\Company;
use App\Entity\CompanyMembership;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CompanyMembership>
 *
 * @method CompanyMembership|null find($id, $lockMode = null, $lockVersion = null)
 * @method CompanyMembership|null findOneBy(array $criteria, array $orderBy = null)
 * @method CompanyMembership[]    findAll()
 * @method CompanyMembership[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompanyMembershipRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CompanyMembership::class);
    }

    public function getCompanyMembershipsByCompany(Company $company)
    {
        //related user name = jules

        return $this->createQueryBuilder('cm')
            ->andWhere('cm.company = :company')
            ->setParameter('company', $company)
            ->getQuery()
        ;
    }

    //    /**
    //     * @return CompanyMembership[] Returns an array of CompanyMembership objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?CompanyMembership
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
