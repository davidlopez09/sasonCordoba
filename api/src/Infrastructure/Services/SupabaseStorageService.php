<?php

namespace App\Infrastructure\Services;

use App\Domain\Interfaces\StorageServiceInterface;
use Exception;

class SupabaseStorageService implements StorageServiceInterface
{
    private $url;
    private $secretKey;

    public function __construct(array $config)
    {
        $this->url = $config['supabase']['url'] ?? '';
        $this->secretKey = $config['supabase']['secret_key'] ?? '';
    }

    public function uploadImage(string $bucket, string $prefix, array $fileInfo): ?string
    {
        if (empty($fileInfo) || $fileInfo['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($fileInfo['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Error al subir el archivo');
        }
        if ($fileInfo['size'] > 2 * 1024 * 1024) {
            throw new Exception('La imagen no debe superar 2MB');
        }
        $allowed = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'svg' => 'image/svg+xml'];
        $ext = strtolower(pathinfo($fileInfo['name'], PATHINFO_EXTENSION));
        if (!isset($allowed[$ext])) {
            throw new Exception('Formato no permitido. Usa JPG, PNG, WEBP o SVG');
        }
        $mime = mime_content_type($fileInfo['tmp_name']);
        if ($ext !== 'svg' && $mime !== $allowed[$ext]) {
            throw new Exception('El archivo no es una imagen válida');
        }

        $filename = $prefix . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        return $this->doUploadToSupabase($bucket, $filename, $fileInfo['tmp_name'], $allowed[$ext]);
    }

    public function deleteImage(string $bucket, string $path): void
    {
        $marker = '/storage/v1/object/public/' . $bucket . '/';
        $pos = strpos($path, $marker);
        if ($pos === false) {
            return;
        }
        $filename = substr($path, $pos + strlen($marker));
        if (!$filename) {
            return;
        }

        $url = rtrim($this->url, '/') . '/storage/v1/object/' . rawurlencode($bucket) . '/' . rawurlencode($filename);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->secretKey,
                'apikey: ' . $this->secretKey,
            ],
            CURLOPT_RETURNTRANSFER => true,
        ]);
        curl_exec($ch);
        curl_close($ch);
    }

    private function doUploadToSupabase(string $bucket, string $filename, string $filePath, string $mimeType): string
    {
        $url = rtrim($this->url, '/') . '/storage/v1/object/' . rawurlencode($bucket) . '/' . rawurlencode($filename);
        $fileContents = file_get_contents($filePath);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $fileContents,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->secretKey,
                'apikey: ' . $this->secretKey,
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

        return rtrim($this->url, '/') . '/storage/v1/object/public/' . rawurlencode($bucket) . '/' . rawurlencode($filename);
    }
}
