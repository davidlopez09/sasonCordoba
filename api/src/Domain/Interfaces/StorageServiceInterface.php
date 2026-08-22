<?php

namespace App\Domain\Interfaces;

interface StorageServiceInterface
{
    public function uploadImage(string $bucket, string $prefix, array $fileInfo): ?string;
    public function deleteImage(string $bucket, string $path): void;
}
