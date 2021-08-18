<?php

namespace App\Repository;

use App\Entity\Messageholder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Messageholder|null find($id, $lockMode = null, $lockVersion = null)
 * @method Messageholder|null findOneBy(array $criteria, array $orderBy = null)
 * @method Messageholder[]    findAll()
 * @method Messageholder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageholderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Messageholder::class);
    }

    // /**
    //  * @return Messageholder[] Returns an array of Messageholder objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Messageholder
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
