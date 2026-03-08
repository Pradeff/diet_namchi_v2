<?php

namespace App\Repository;

use App\Entity\Vabout;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vabout>
 */
class VaboutRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vabout::class);
    }

    public function save(Vabout $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Vabout $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Find the first (and should be only) Vabout record
     * This enforces the singleton pattern
     */
    public function findFirst(): ?Vabout
    {
        return $this->createQueryBuilder('v')
            ->orderBy('v.created_at', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Check if any Vabout record exists
     */
    public function exists(): bool
    {
        return $this->count([]) > 0;
    }

    /**
     * Get the singleton instance or return null if none exists
     */
    public function getInstance(): ?Vabout
    {
        return $this->findFirst();
    }
}
