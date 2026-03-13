<?php

namespace App\Repository;

use App\Entity\Vnotice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vnotice>
 */
class VnoticeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vnotice::class);
    }

    /**
     * Returns notices grouped by year for the archive sidebar, excluding currentYear.
     * [ 2025 => [Vnotice, ...], 2024 => [...] ]
     */
    public function findGroupedByYear(int $currentYear): array
    {
        $startOfCurrentYear = new \DateTimeImmutable("{$currentYear}-01-01 00:00:00");

        $notices = $this->createQueryBuilder('v')
            ->where('v.noticeDate < :startOfCurrentYear')
            ->setParameter('startOfCurrentYear', $startOfCurrentYear)
            ->orderBy('v.noticeDate', 'DESC')
            ->getQuery()
            ->getResult();

        $grouped = [];
        foreach ($notices as $notice) {
            if ($notice->getNoticeDate()) {
                $year = (int) $notice->getNoticeDate()->format('Y');
                $grouped[$year][] = $notice;
            }
        }

        return $grouped;
    }

    /**
     * Paginated notices for the current year.
     */
    public function findCurrentYearPaginated(int $currentYear, int $page, int $limit): array
    {
        $startOfYear = new \DateTimeImmutable("{$currentYear}-01-01 00:00:00");
        $endOfYear   = new \DateTimeImmutable("{$currentYear}-12-31 23:59:59");

        return $this->createQueryBuilder('v')
            ->where('v.noticeDate BETWEEN :startOfYear AND :endOfYear')
            ->setParameter('startOfYear', $startOfYear)
            ->setParameter('endOfYear', $endOfYear)
            ->orderBy('v.noticeDate', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Total notices count for current year (pagination).
     */
    public function countCurrentYear(int $currentYear): int
    {
        $startOfYear = new \DateTimeImmutable("{$currentYear}-01-01 00:00:00");
        $endOfYear   = new \DateTimeImmutable("{$currentYear}-12-31 23:59:59");

        return (int) $this->createQueryBuilder('v')
            ->select('COUNT(v.id)')
            ->where('v.noticeDate BETWEEN :startOfYear AND :endOfYear')
            ->setParameter('startOfYear', $startOfYear)
            ->setParameter('endOfYear', $endOfYear)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
