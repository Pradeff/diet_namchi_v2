<?php

namespace App\Repository;

use App\Entity\Vpeople;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vpeople>
 */
class VpeopleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vpeople::class);
    }

    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('v')
            ->orderBy('v.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getMaxPosition(): int
    {
        $result = $this->createQueryBuilder('v')
            ->select('MAX(v.position)')
            ->getQuery()
            ->getSingleScalarResult();

        return $result ?? 0;
    }

    public function reorderPositions(): void
    {
        $entities = $this->findAllOrdered();
        $position = 1;

        foreach ($entities as $entity) {
            $entity->setPosition($position++);
        }

        $this->getEntityManager()->flush();
    }
    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('v')
            ->select('COUNT(v.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getSingle(): ?Vpeople
    {
        return $this->createQueryBuilder('v')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

}
