<?php

namespace App\Repository;

use App\Entity\GroupT;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GroupT|null find($id, $lockMode = null, $lockVersion = null)
 * @method GroupT|null findOneBy(array $criteria, array $orderBy = null)
 * @method GroupT[]    findAll()
 * @method GroupT[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GroupTRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GroupT::class);
    }

    // /**
    //  * @return GroupT[] Returns an array of GroupT objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?GroupT
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
