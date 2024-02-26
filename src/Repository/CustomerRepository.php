<?php

namespace App\Repository;

use App\Entity\Customer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<Customer>
 *
 * @implements PasswordUpgraderInterface<Customer>
 *
 * @method Customer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Customer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Customer[]    findAll()
 * @method Customer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CustomerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Customer::class);
    }

    public function getCustomersByFilters($company = null, $options = [])
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
                foreach (['c.firstName', 'c.lastName'] as $f) {
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
