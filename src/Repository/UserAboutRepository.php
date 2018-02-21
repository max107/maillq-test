<?php

namespace App\Repository;

use App\Entity\UserAbout;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UserAbout|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserAbout|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserAbout[]    findAll()
 * @method UserAbout[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserAboutRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserAbout::class);
    }

    /*
    public function findBySomething($value)
    {
        return $this->createQueryBuilder('u')
            ->where('u.something = :value')->setParameter('value', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */
}
