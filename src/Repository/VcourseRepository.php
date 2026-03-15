<?php

namespace App\Repository;

use App\Entity\Vcourse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class VcourseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vcourse::class);
    }

    public function save(Vcourse $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Vcourse $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAllOrderedByCreatedAt(): array
    {
        return $this->createQueryBuilder('v')
            ->orderBy('v.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneBySlug(string $slug): ?Vcourse
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findLatest(int $limit = 6): array
    {
        return $this->createQueryBuilder('v')
            ->orderBy('v.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findLatestWithCover(int $limit = 3): array
    {
        return $this->createQueryBuilder('v')
            ->where('v.coverImage IS NOT NULL')
            ->andWhere('v.coverImage != :empty')
            ->setParameter('empty', '')
            ->orderBy('v.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findLatestWithImages(int $limit = 6): array
    {
        $qb = $this->createQueryBuilder('v');

        // PHP-level filtering for JSON array (cross-database)
        $allCourses = $qb
            ->orderBy('v.createdAt', 'DESC')
            ->setMaxResults($limit * 2) // Get more, filter in PHP
            ->getQuery()
            ->getResult();

        // Filter courses that have images array with length > 0
        return array_filter($allCourses, function($course) {
            return !empty($course->getImages());
        });
    }
}
