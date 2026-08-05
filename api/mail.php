<?php

function sendConfirmationEmail(string $to, string $subject, string $body): bool
{
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "From: Sazón Córdoba <no-reply@sazoncordoba.com>\r\n";
    return @mail($to, $subject, $body, $headers);
}
