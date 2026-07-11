<?php

function uploadToSupabaseStorage(string $bucket, string $filename, string $filePath, string $mimeType): string
{
    $config = require __DIR__ . '/config.php';
    $supabase = $config['supabase'];

    $url = rtrim($supabase['url'], '/') . '/storage/v1/object/' . rawurlencode($bucket) . '/' . rawurlencode($filename);
    $fileContents = file_get_contents($filePath);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $fileContents,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $supabase['secret_key'],
            'apikey: ' . $supabase['secret_key'],
            'Content-Type: ' . $mimeType,
            'x-upsert: true',
        ],
        CURLOPT_RETURNTRANSFER => true,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        throw new Exception('Error de conexión con Supabase Storage: ' . $curlError);
    }
    if ($httpCode !== 200) {
        $body = json_decode($response, true);
        throw new Exception($body['message'] ?? $body['error'] ?? "Supabase Storage respondió HTTP $httpCode");
    }

    return rtrim($supabase['url'], '/') . '/storage/v1/object/public/' . rawurlencode($bucket) . '/' . rawurlencode($filename);
}
