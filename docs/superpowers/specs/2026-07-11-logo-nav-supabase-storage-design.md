# Logo del Nav en Supabase Storage

## Contexto

El logo del navbar (`img.brand-logo`) se sube desde el panel admin (`api/admin/index.php`, acción `save_logo_nav`) guardando el archivo en el filesystem local (`img/logos/`) y persistiendo una ruta **relativa** (`img/logos/logosason.jpg`) en `configuraciones_sitio.logo_nav`.

Esa ruta relativa es el origen de un bug: en el sitio público (`index.html`, en la raíz) resuelve correctamente, pero en el panel admin (`api/admin/index.php`, dentro de `api/admin/`) el navegador la resuelve como `api/admin/img/logos/logosason.jpg`, que no existe — por eso el preview del logo no se ve en el panel.

Además, se pidió dejar de guardar imágenes en el filesystem del repo y usar Supabase Storage, empezando por este logo.

## Objetivo

1. Subir el logo del nav a un bucket de Supabase Storage en vez del filesystem local.
2. Guardar la URL pública completa en `configuraciones_sitio.logo_nav`, resolviendo el bug de la ruta relativa como efecto colateral.
3. Migrar el logo actualmente en uso (`img/logos/logosason.jpg`) al bucket, para no romper el sitio.

## Alcance

Solo el logo del nav (`logo_nav`). Las demás imágenes del sitio (slides, exponentes, platillos, patrocinadores, footer) **no** se migran en este trabajo — siguen usando el filesystem local como hoy.

## Infraestructura Supabase

- Project URL: `https://eztujvwjoihdjrpqglhk.supabase.co`
- Bucket: `logo_nav` (ya existe, `public: true`, sin límite de tamaño ni restricción de mime type configurados en el bucket — la validación de tamaño/tipo se sigue haciendo en el backend PHP antes de subir).
- Autenticación: `secret` API key de Supabase (reemplazo de `service_role`), usada **solo server-side** (PHP), nunca expuesta al navegador.

## Cambios

### 1. `api/config.php`

Agrega un bloque `supabase`:

```php
'supabase' => [
    'url' => 'https://eztujvwjoihdjrpqglhk.supabase.co',
    'secret_key' => '(valor real solo en api/config.php, que está en .gitignore)',
],
```

### 2. Nuevo archivo `api/supabase_storage.php`

Una función `uploadToSupabaseStorage(string $bucket, string $filename, string $filePath, string $mimeType): string` que:

- Lee el archivo local (`file_get_contents($filePath)`).
- Hace `POST {SUPABASE_URL}/storage/v1/object/{bucket}/{filename}` con headers `Authorization: Bearer {secret_key}`, `apikey: {secret_key}`, `Content-Type: {mimeType}`, y el body binario del archivo (vía cURL).
- Si la respuesta HTTP no es 200, lanza una `Exception` con el mensaje de error que devuelve Supabase.
- Si es 200, devuelve la URL pública: `{SUPABASE_URL}/storage/v1/object/public/{bucket}/{filename}`.

### 3. `api/admin/index.php` — acción `save_logo_nav`

Se mantienen las validaciones actuales (tamaño ≤2MB, extensión permitida, mime real verificado con `mime_content_type`). Donde hoy se hace:

```php
$filename = 'nav_logo_' . bin2hex(random_bytes(6)) . '.' . $ext;
if (!move_uploaded_file($file['tmp_name'], __DIR__ . '/../../img/logos/' . $filename)) {
    jsonResponse(['error' => 'No se pudo guardar el archivo']);
}
$updates['logo_nav'] = 'img/logos/' . $filename;
```

pasa a:

```php
$filename = 'nav_logo_' . bin2hex(random_bytes(6)) . '.' . $ext;
try {
    $updates['logo_nav'] = uploadToSupabaseStorage('logo_nav', $filename, $file['tmp_name'], $allowed[$ext]);
} catch (Exception $e) {
    jsonResponse(['error' => 'No se pudo subir el archivo: ' . $e->getMessage()]);
}
```

requiere `require __DIR__ . '/../supabase_storage.php';` al inicio del archivo.

### 4. Migración del logo actual

Se sube el archivo local `img/logos/logosason.jpg` al bucket `logo_nav` (mismo mecanismo, ejecutado una sola vez vía script) y se actualiza `UPDATE configuraciones_sitio SET valor = '{url pública}' WHERE clave = 'logo_nav'`, para que el valor ya activo en producción apunte al bucket desde el día uno.

## Fuera de alcance

- No se borra `img/logos/logosason.jpg` del repo.
- No se migran otras imágenes del sitio (solo el logo del nav).
- No se agregan políticas RLS de Storage (el bucket es público de lectura por configuración propia del bucket; las escrituras van siempre server-side con la `secret` key, sin exponer credenciales al cliente).

## Testing

Manual:

1. Recargar el sitio público y el panel admin — confirmar que el logo se ve en ambos (usando la URL migrada).
2. Desde el panel, subir un logo nuevo (imagen de prueba) — confirmar que el preview se actualiza en el panel y en el sitio público tras recargar.
3. Confirmar en `configuraciones_sitio` que el valor de `logo_nav` es una URL `https://eztujvwjoihdjrpqglhk.supabase.co/storage/v1/object/public/logo_nav/...`.
4. Probar un archivo con extensión no permitida (ej. `.gif`) — confirmar que se rechaza con el mismo mensaje de error que antes, sin llegar a intentar subir a Supabase.
