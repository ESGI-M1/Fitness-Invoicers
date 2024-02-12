<?php

namespace App\Repository;

use App\Entity\Deposit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Deposit>
 *
 * @method Deposit|null find($id, $lockMode = null, $lockVersion = null)
 * @method Deposit|null findOneBy(array $criteria, array $orderBy = null)
 * @method Deposit[]    findAll()
 * @method Deposit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DepositRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Deposit::class);
    }

    /**
     * @return Deposit[] Returns an array of Deposit objects
     */
    public function getDepositFromCompany($company, $options = []): array
    {
        $query = $this->createQueryBuilder('d')
            ->leftJoin('d.quote', 'quotes')
            ->innerJoin('quotes.invoices', 'invoices')
            ->andWhere('quotes.company IN (:company)')
            ->setParameter('company', $company);

        if (isset($options['price']) && $options['price']) {
            $query->andWhere('d.price = :price')
                ->setParameter('price', $options['price']);
        }
        return $query
            ->getQuery()
            ->getResult();
    }

}
