<?php

function sendConfirmationEmail(string $to, string $subject, string $body): bool
{
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "From: Sazón Córdoba <no-reply@sazoncordoba.com>\r\n";
    
    $result = mail($to, $subject, $body, $headers);
    if (!$result) {
        error_log("Error enviando correo de confirmación a $to con el asunto: $subject");
    }
    return $result;
}
