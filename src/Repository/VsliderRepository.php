<?php

namespace App\Repository;

use App\Entity\Vslider;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vslider>
 */
class VsliderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vslider::class);
    }

    public function save(Vslider $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Vslider $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAllOrderedBySortOrder(): array
    {
        return $this->createQueryBuilder('v')
            ->where('v.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('v.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
