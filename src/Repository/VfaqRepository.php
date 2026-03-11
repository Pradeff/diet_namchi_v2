<?php
// src/Repository/VfaqRepository.php
namespace App\Repository;

use App\Entity\Vfaq;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vfaq>
 */
class VfaqRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vfaq::class);
    }

    /**
     * Return all FAQs ordered by creation date (oldest first — stable display order).
     *
     * @return Vfaq[]
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('f')
            ->orderBy('f.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findFirstThreeOrdered(): array
    {
        return $this->createQueryBuilder('f')
            ->orderBy('f.createdAt', 'ASC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();
    }
}
