<?php

namespace App\Application;

use App\Domain\Interfaces\SiteDataRepositoryInterface;

class GetSiteDataUseCase
{
    private $repository;

    public function __construct(SiteDataRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(): array
    {
        return $this->repository->getSiteData();
    }
    
    public function getNavData(): array
    {
        return $this->repository->getNavData();
    }

    public function getTerminos(): string
    {
        return $this->repository->getTerminos();
    }
}
