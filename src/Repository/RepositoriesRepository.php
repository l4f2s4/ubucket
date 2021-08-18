<?php

namespace App\Repository;

use App\Entity\Repositories;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Repositories|null find($id, $lockMode = null, $lockVersion = null)
 * @method Repositories|null findOneBy(array $criteria, array $orderBy = null)
 * @method Repositories[]    findAll()
 * @method Repositories[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RepositoriesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Repositories::class);
    }

    // /**
    //  * @return Repositories[] Returns an array of Repositories objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Repositories
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
