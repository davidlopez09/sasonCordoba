<?php

namespace App\Presentation;

use App\Application\Admin\Setting\UpdateSettingUseCase;
use Exception;

class AdminSettingsController
{
    private $updateUseCase;

    public function __construct(UpdateSettingUseCase $updateUseCase)
    {
        $this->updateUseCase = $updateUseCase;
    }

    public function handleRequest(string $method)
    {
        if ($method !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Metodo no permitido']);
            exit;
        }

        $action = $_POST['action'] ?? '';

        try {
            $this->updateUseCase->execute($action, $_POST, $_FILES);
            http_response_code(200);
            echo json_encode(['ok' => true]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }
}
