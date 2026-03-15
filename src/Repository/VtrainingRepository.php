<?php

namespace App\Repository;

use App\Entity\Vtraining;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vtraining>
 */
class VtrainingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vtraining::class);
    }

    /**
     * Returns all trainings ordered by createdAt descending.
     *
     * @return Vtraining[]
     */
    public function findAllOrderedByCreatedAt(): array
    {
        return $this->createQueryBuilder('v')
            ->orderBy('v.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
