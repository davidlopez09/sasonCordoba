<?php

namespace App\Domain\Interfaces;

interface SiteDataRepositoryInterface
{
    public function getTerminos(): string;
    public function getNavData(): array;
    public function getSiteData(): array;
}
