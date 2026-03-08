<?php
// src/Twig/ContactGlobalExtension.php

namespace App\Twig;

use App\Repository\VcontactRepository;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class ContactGlobalExtension extends AbstractExtension implements GlobalsInterface
{
    private VcontactRepository $vcontactRepository;

    public function __construct(VcontactRepository $vcontactRepository)
    {
        $this->vcontactRepository = $vcontactRepository;
    }

    public function getGlobals(): array
    {
        $globalContact = $this->vcontactRepository->findOneBy([]);

        return [
            'global_contact' => $globalContact,
        ];
    }
}
