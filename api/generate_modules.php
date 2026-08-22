<?php

$modules = [
    'Footer' => [
        'table' => 'pie_pagina',
        'has_image' => false,
        'fields' => ['tipo', 'titulo', 'contenido', 'url', 'icono', 'columna', 'color'],
        'required' => ['tipo', 'columna']
    ],
    'SeccionDinamica' => [
        'table' => 'secciones_dinamicas',
        'has_image' => false,
        'fields' => ['nombre', 'insertar_despues', 'activo'],
        'required' => ['nombre']
    ],
    'BloqueDinamico' => [
        'table' => 'bloques_dinamicos',
        'has_image' => false,
        'fields' => ['seccion_id', 'tipo', 'posicion', 'contenido'],
        'required' => ['seccion_id', 'tipo']
    ],
    'BotonParticipa' => [
        'table' => 'botones_participa',
        'has_image' => false,
        'fields' => ['texto', 'enlace', 'color_fondo', 'color_texto', 'color_borde', 'activo'],
        'required' => ['texto']
    ],
    'Directorio' => [
        'table' => 'directorio_expositores',
        'has_image' => true,
        'image_field' => 'logo',
        'bucket' => 'logos_directorio', // the original script didn't have an upload for directorio? Wait!
        'prefix' => 'directorio',
        'fields' => ['nombre', 'categoria', 'descripcion', 'contacto', 'activo', 'color'],
        'required' => ['nombre']
    ],
    'Galeria' => [
        'table' => 'galeria_items',
        'has_image' => false, // Actually the url field might just be a text field or upload. The original admin panel uses text url for galeria!
        'fields' => ['tipo', 'url', 'titulo', 'edicion', 'activo'],
        'required' => ['tipo', 'url']
    ]
];

$basePath = __DIR__ . '/src';

foreach ($modules as $name => $cfg) {
    // 1. Interface
    $interface = "<?php\n\nnamespace App\\Domain\\Interfaces;\n\ninterface {$name}RepositoryInterface\n{\n    public function getAll(): array;\n    public function getById(int \$id): ?array;\n    public function create(array \$data): void;\n    public function update(int \$id, array \$data): void;\n    public function delete(int \$id): void;\n    public function getCount(): int;\n    public function shiftForInsert(int \$orden): void;\n    public function shiftForUpdate(int \$oldOrden, int \$newOrden): void;\n    public function shiftForDelete(int \$orden): void;\n}\n";
    file_put_contents("$basePath/Domain/Interfaces/{$name}RepositoryInterface.php", $interface);

    // 2. Repository
    $repo = "<?php\n\nnamespace App\\Infrastructure\\Repositories;\n\nuse App\\Domain\\Interfaces\\{$name}RepositoryInterface;\nuse Illuminate\\Database\\Capsule\\Manager as DB;\n\nclass Eloquent{$name}Repository implements {$name}RepositoryInterface\n{\n    private \$table = '{$cfg['table']}';\n\n    public function getAll(): array\n    {\n        return DB::table(\$this->table)->orderBy('orden')->get()->toArray();\n    }\n\n    public function getById(int \$id): ?array\n    {\n        \$result = DB::table(\$this->table)->where('id', \$id)->first();\n        return \$result ? (array)\$result : null;\n    }\n\n    public function create(array \$data): void\n    {\n        DB::table(\$this->table)->insert(\$data);\n    }\n\n    public function update(int \$id, array \$data): void\n    {\n        DB::table(\$this->table)->where('id', \$id)->update(\$data);\n    }\n\n    public function delete(int \$id): void\n    {\n        DB::table(\$this->table)->where('id', \$id)->delete();\n    }\n\n    public function getCount(): int\n    {\n        return DB::table(\$this->table)->count();\n    }\n\n    public function shiftForInsert(int \$orden): void\n    {\n        DB::table(\$this->table)->where('orden', '>=', \$orden)->increment('orden');\n    }\n\n    public function shiftForUpdate(int \$oldOrden, int \$newOrden): void\n    {\n        if (\$newOrden === \$oldOrden) return;\n        if (\$newOrden < \$oldOrden) {\n            DB::table(\$this->table)->where('orden', '>=', \$newOrden)->where('orden', '<', \$oldOrden)->increment('orden');\n        } else {\n            DB::table(\$this->table)->where('orden', '>', \$oldOrden)->where('orden', '<=', \$newOrden)->decrement('orden');\n        }\n    }\n\n    public function shiftForDelete(int \$orden): void\n    {\n        DB::table(\$this->table)->where('orden', '>', \$orden)->decrement('orden');\n    }\n}\n";
    file_put_contents("$basePath/Infrastructure/Repositories/Eloquent{$name}Repository.php", $repo);

    // 3. Use Cases
    @mkdir("$basePath/Application/Admin/{$name}", 0777, true);
    
    // Create
    $hasImage = $cfg['has_image'];
    $imgField = $hasImage ? $cfg['image_field'] : '';
    $bucket = $hasImage ? $cfg['bucket'] : '';
    $prefix = $hasImage ? $cfg['prefix'] : '';
    
    $reqChecks = [];
    foreach ($cfg['required'] as $r) {
        $reqChecks[] = "empty(\$data['$r'])";
    }
    $reqCheckStr = empty($reqChecks) ? "false" : implode(' || ', $reqChecks);

    $fieldsAssign = [];
    foreach ($cfg['fields'] as $f) {
        $fieldsAssign[] = "            '$f' => isset(\$data['$f']) ? \$data['$f'] : null,";
    }
    $fieldsAssignStr = implode("\n", $fieldsAssign);

    $imgUploadCreate = $hasImage ? "\n        \$fotoUrl = \$this->storageService->uploadImage('$bucket', '$prefix', \$fileInfo);\n        if (!\$fotoUrl) throw new Exception('Debés subir una imagen/logo');\n" : "";
    $imgAssignCreate = $hasImage ? "            '$imgField' => \$fotoUrl,\n" : "";

    $createUC = "<?php\n\nnamespace App\\Application\\Admin\\{$name};\n\nuse App\\Domain\\Interfaces\\{$name}RepositoryInterface;\n" . ($hasImage ? "use App\\Domain\\Interfaces\\StorageServiceInterface;\n" : "") . "use Exception;\n\nclass Create{$name}UseCase\n{\n    private \$repository;\n" . ($hasImage ? "    private \$storageService;\n" : "") . "\n    public function __construct({$name}RepositoryInterface \$repository" . ($hasImage ? ", StorageServiceInterface \$storageService" : "") . ")\n    {\n        \$this->repository = \$repository;\n" . ($hasImage ? "        \$this->storageService = \$storageService;\n" : "") . "    }\n\n    public function execute(array \$data" . ($hasImage ? ", array \$fileInfo" : "") . "): void\n    {\n        if ($reqCheckStr) {\n            throw new Exception(\"Faltan campos obligatorios\");\n        }\n$imgUploadCreate\n        \$count = \$this->repository->getCount();\n        \$requestedOrden = isset(\$data['orden']) ? (int)\$data['orden'] : (\$count + 1);\n        \$orden = max(1, min(\$count + 1, \$requestedOrden));\n\n        \$this->repository->shiftForInsert(\$orden);\n\n        \$this->repository->create([\n$imgAssignCreate$fieldsAssignStr\n            'orden' => \$orden\n        ]);\n    }\n}\n";
    file_put_contents("$basePath/Application/Admin/{$name}/Create{$name}UseCase.php", $createUC);

    // Update
    $imgUploadUpdate = $hasImage ? "        \$fotoUrl = null;\n        if (\$fileInfo && isset(\$fileInfo['error']) && \$fileInfo['error'] !== UPLOAD_ERR_NO_FILE) {\n            \$fotoUrl = \$this->storageService->uploadImage('$bucket', '$prefix', \$fileInfo);\n            if (\$fotoUrl && \$current['$imgField']) {\n                \$this->storageService->deleteImage('$bucket', \$current['$imgField']);\n            }\n        }\n" : "";
    $imgAssignUpdate = $hasImage ? "        if (\$fotoUrl) \$updateData['$imgField'] = \$fotoUrl;\n" : "";

    $updateUC = "<?php\n\nnamespace App\\Application\\Admin\\{$name};\n\nuse App\\Domain\\Interfaces\\{$name}RepositoryInterface;\n" . ($hasImage ? "use App\\Domain\\Interfaces\\StorageServiceInterface;\n" : "") . "use Exception;\n\nclass Update{$name}UseCase\n{\n    private \$repository;\n" . ($hasImage ? "    private \$storageService;\n" : "") . "\n    public function __construct({$name}RepositoryInterface \$repository" . ($hasImage ? ", StorageServiceInterface \$storageService" : "") . ")\n    {\n        \$this->repository = \$repository;\n" . ($hasImage ? "        \$this->storageService = \$storageService;\n" : "") . "    }\n\n    public function execute(int \$id, array \$data" . ($hasImage ? ", ?array \$fileInfo" : "") . "): void\n    {\n        if ($reqCheckStr) {\n            throw new Exception(\"Faltan campos obligatorios\");\n        }\n\n        \$current = \$this->repository->getById(\$id);\n        if (!\$current) throw new Exception(\"Registro no encontrado\");\n\n$imgUploadUpdate\n        \$oldOrden = (int) \$current['orden'];\n        \$count = \$this->repository->getCount();\n        \$requestedOrden = isset(\$data['orden']) ? (int)\$data['orden'] : \$oldOrden;\n        \$orden = max(1, min(\$count, \$requestedOrden));\n\n        \$this->repository->shiftForUpdate(\$oldOrden, \$orden);\n\n        \$updateData = [\n$fieldsAssignStr\n            'orden' => \$orden\n        ];\n\n$imgAssignUpdate\n        \$this->repository->update(\$id, \$updateData);\n    }\n}\n";
    file_put_contents("$basePath/Application/Admin/{$name}/Update{$name}UseCase.php", $updateUC);

    // Delete
    $imgDelete = $hasImage ? "        if (\$current['$imgField']) {\n            \$this->storageService->deleteImage('$bucket', \$current['$imgField']);\n        }\n" : "";
    $deleteUC = "<?php\n\nnamespace App\\Application\\Admin\\{$name};\n\nuse App\\Domain\\Interfaces\\{$name}RepositoryInterface;\n" . ($hasImage ? "use App\\Domain\\Interfaces\\StorageServiceInterface;\n" : "") . "use Exception;\n\nclass Delete{$name}UseCase\n{\n    private \$repository;\n" . ($hasImage ? "    private \$storageService;\n" : "") . "\n    public function __construct({$name}RepositoryInterface \$repository" . ($hasImage ? ", StorageServiceInterface \$storageService" : "") . ")\n    {\n        \$this->repository = \$repository;\n" . ($hasImage ? "        \$this->storageService = \$storageService;\n" : "") . "    }\n\n    public function execute(int \$id): void\n    {\n        \$current = \$this->repository->getById(\$id);\n        if (!\$current) throw new Exception(\"Registro no encontrado\");\n\n$imgDelete\n        \$oldOrden = (int) \$current['orden'];\n        \$this->repository->delete(\$id);\n        \$this->repository->shiftForDelete(\$oldOrden);\n    }\n}\n";
    file_put_contents("$basePath/Application/Admin/{$name}/Delete{$name}UseCase.php", $deleteUC);

    // 4. Controller
    $controller = "<?php\n\nnamespace App\\Presentation;\n\nuse App\\Application\\Admin\\{$name}\\Create{$name}UseCase;\nuse App\\Application\\Admin\\{$name}\\Update{$name}UseCase;\nuse App\\Application\\Admin\\{$name}\\Delete{$name}UseCase;\nuse App\\Domain\\Interfaces\\{$name}RepositoryInterface;\nuse Exception;\n\nclass Admin{$name}sController\n{\n    private \$createUseCase;\n    private \$updateUseCase;\n    private \$deleteUseCase;\n    private \$repository;\n\n    public function __construct(\n        Create{$name}UseCase \$createUseCase,\n        Update{$name}UseCase \$updateUseCase,\n        Delete{$name}UseCase \$deleteUseCase,\n        {$name}RepositoryInterface \$repository\n    ) {\n        \$this->createUseCase = \$createUseCase;\n        \$this->updateUseCase = \$updateUseCase;\n        \$this->deleteUseCase = \$deleteUseCase;\n        \$this->repository = \$repository;\n    }\n\n    public function handleRequest(string \$method, array \$pathParts)\n    {\n        try {\n            \$id = isset(\$pathParts[3]) ? (int)\$pathParts[3] : null;\n            \$actualMethod = \$method;\n            if (\$method === 'POST' && isset(\$_POST['_method'])) \$actualMethod = strtoupper(\$_POST['_method']);\n\n            if (\$actualMethod === 'GET' && !\$id) return \$this->jsonResponse(\$this->repository->getAll());\n            if (\$actualMethod === 'POST' && !\$id) {\n                \$this->createUseCase->execute(\$_POST" . ($hasImage ? ", \$_FILES['$imgField'] ?? []" : "") . ");\n                return \$this->jsonResponse(['ok' => true]);\n            }\n            if (\$actualMethod === 'PUT' && \$id) {\n                \$this->updateUseCase->execute(\$id, \$_POST" . ($hasImage ? ", \$_FILES['$imgField'] ?? null" : "") . ");\n                return \$this->jsonResponse(['ok' => true]);\n            }\n            if (\$actualMethod === 'DELETE' && \$id) {\n                \$this->deleteUseCase->execute(\$id);\n                return \$this->jsonResponse(['ok' => true]);\n            }\n            return \$this->jsonError('Ruta no encontrada', 404);\n        } catch (Exception \$e) {\n            return \$this->jsonError(\$e->getMessage(), 500);\n        }\n    }\n\n    private function jsonResponse(array \$data, int \$code = 200)\n    {\n        http_response_code(\$code);\n        echo json_encode(\$data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);\n        exit;\n    }\n\n    private function jsonError(string \$message, int \$code = 400)\n    {\n        \$this->jsonResponse(['error' => \$message], \$code);\n    }\n}\n";
    file_put_contents("$basePath/Presentation/Admin{$name}sController.php", $controller);
}

echo "Files generated successfully.\n";
