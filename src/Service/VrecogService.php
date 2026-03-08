<?php

namespace App\Service;

use App\Repository\VrecogRepository;

class VrecogService
{
    public function __construct(private VrecogRepository $vrecogRepository)
    {
    }

    public function getFooterIcons(): array
    {
        return $this->vrecogRepository->findAllOrderedByTitle();
    }
}
