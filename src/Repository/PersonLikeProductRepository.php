<?php

namespace App\Repository;

use App\Entity\PersonLikeProduct;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PersonLikeProduct|null find($id, $lockMode = null, $lockVersion = null)
 * @method PersonLikeProduct|null findOneBy(array $criteria, array $orderBy = null)
 * @method PersonLikeProduct[]    findAll()
 * @method PersonLikeProduct[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PersonLikeProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PersonLikeProduct::class);
    }

    // /**
    //  * @return PersonLikeProduct[] Returns an array of PersonLikeProduct objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PersonLikeProduct
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
