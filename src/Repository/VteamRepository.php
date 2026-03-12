<?php

namespace App\Repository;

use App\Entity\Vteam;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vteam>
 *
 * @method Vteam|null find($id, $lockMode = null, $lockVersion = null)
 * @method Vteam|null findOneBy(array $criteria, array $orderBy = null)
 * @method Vteam[]    findAll()
 * @method Vteam[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VteamRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vteam::class);
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
        return (int) $this->createQueryBuilder('v')
            ->select('MAX(v.position)')
            ->getQuery()
            ->getSingleScalarResult();
    }


    public function reorderPositions(): void
    {
        $vteams = $this->createQueryBuilder('v')
            ->orderBy('v.position', 'ASC')
            ->getQuery()
            ->getResult();

        foreach ($vteams as $index => $vteam) {
            $vteam->setPosition($index + 1); // Ensure sequential order
        }

        $this->getEntityManager()->flush(); // Correctly flush changes
    }

    public function findTopN(int $limit = 3): array
    {
        return $this->createQueryBuilder('v')
            ->orderBy('v.position', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findPrincipal(): ?Vteam
    {
        return $this->createQueryBuilder('v')
            ->orderBy('v.position', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }



//    /**
//     * @return Vteam[] Returns an array of Vteam objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('v.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Vteam
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
