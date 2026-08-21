<?php

namespace App\Domain\Interfaces;

interface MailServiceInterface
{
    public function sendConfirmation(string $to, string $subject, string $body): bool;
}
