<?php

namespace App\Presentation;

use App\Application\Admin\Export\ExportVisitorsUseCase;
use App\Application\Admin\Export\ExportExhibitorsUseCase;
use Exception;

class AdminExportController
{
    private $exportVisitorsUseCase;
    private $exportExhibitorsUseCase;

    public function __construct(
        ExportVisitorsUseCase $exportVisitorsUseCase,
        ExportExhibitorsUseCase $exportExhibitorsUseCase
    ) {
        $this->exportVisitorsUseCase = $exportVisitorsUseCase;
        $this->exportExhibitorsUseCase = $exportExhibitorsUseCase;
    }

    public function handleRequest(string $method, array $pathParts)
    {
        if ($method !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Metodo no permitido']);
            exit;
        }

        $resource = $pathParts[3] ?? '';

        try {
            if ($resource === 'visitors') {
                $data = $this->exportVisitorsUseCase->execute();
                $this->outputCsv('registros_visitantes.csv', $data);
            } elseif ($resource === 'exhibitors') {
                $data = $this->exportExhibitorsUseCase->execute();
                $this->outputCsv('registros_expositores.csv', $data);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Recurso a exportar no encontrado']);
                exit;
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
    }

    private function outputCsv(string $filename, array $data)
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $out = fopen('php://output', 'w');
        // Escribir BOM para Excel
        fputs($out, "\xEF\xBB\xBF");
        
        foreach ($data as $row) {
            fputcsv($out, $row);
        }
        
        fclose($out);
        exit;
    }
}
