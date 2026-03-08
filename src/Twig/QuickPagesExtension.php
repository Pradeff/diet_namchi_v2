<?php
// src/Twig/QuickPagesExtension.php

namespace App\Twig;

use App\Entity\Vpages;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class QuickPagesExtension extends AbstractExtension implements GlobalsInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getGlobals(): array
    {
        $quickLinkPages = $this->entityManager->getRepository(Vpages::class)->findBy(
            [],
            ['title' => 'ASC']
        );

        return [
            'quick_link_pages' => $quickLinkPages,
        ];
    }
}
