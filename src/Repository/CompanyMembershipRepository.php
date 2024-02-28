<?php

namespace App\Repository;

use App\Entity\Company;
use App\Entity\CompanyMembership;
use App\Entity\User;
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

    public function getUsersMembershipsByFilters(Company $company)
    {
        $query = $this->createQueryBuilder('cm')
            ->leftJoin('cm.relatedUser', 'users')
            ->andWhere('cm.company = :company')
            ->setParameter('company', $company);

        if (isset($options['alias']) && $options['alias'] !== '') {
            $parts = explode(' ', $options['alias']);
            $subAnd = [];
            foreach ($parts as $k => $p) {
                $tag = 'alias_' . $k;
                $subOr = [];
                foreach (['users.lastName', 'users.firstName'] as $f) {
                    $subOr[] = "{$f} LIKE :{$tag}";
                }
                $subAnd[] = '(' . implode(' OR ', $subOr) . ')';
                $query->setParameter($tag, "%$p%");
            }
            $query
                ->andWhere('(' . implode(' AND ', $subAnd) . ')');
        }
        if (isset($options['email']) && $options['email']) {
            $query
                ->andWhere('users.email LIKE :email')
                ->setParameter('email', '%' . $options['email'] . '%');
        }
        if (isset($options['sexe']) && $options['sexe']) {
            $query
                ->andWhere('users.sexe = :sexe')
                ->setParameter('sexe', $options['sexe']->name);
        }

        $query->orderBy('users.lastName', 'ASC')
            ->orderBy('users.firstName', 'ASC');

        return $query
            ->getQuery();
    }

    public function getCompanyMembershipsByCompanyAndUser(Company $company, User $user)
    {
        return $this->createQueryBuilder('cm')
            ->andWhere('cm.company = :company')
            ->andWhere('cm.relatedUser = :user')
            ->setParameter('company', $company)
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
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
