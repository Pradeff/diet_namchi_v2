<?php
// src/Repository/VpagesRepository.php

namespace App\Repository;

use App\Entity\Vpages;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vpages>
 */
class VpagesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vpages::class);
    }

    /**
     * Find all pages ordered by title for Quick Links
     */
    public function findAllOrderedByTitle(): array
    {
        return $this->createQueryBuilder('v')
            ->orderBy('v.title', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find page by slug
     */
    public function findOneBySlug(string $slug): ?Vpages
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find all active pages (if you have isActive field)
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('v')
            ->orderBy('v.title', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
