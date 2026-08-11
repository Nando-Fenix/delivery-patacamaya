# AGENTS.md

## Proyecto

Delivery Patacamaya es un monolito Laravel 13 para un MVP académico de delivery.
Usa PHP 8.3+, Blade, Bootstrap 5, Bootstrap Icons, JavaScript nativo, Vite,
MySQL 8 y una base PWA. El producto y la interfaz están en español.

## Límites y arquitectura

- Tratar `C:\laragon\www\delivery-patacamaya` como raíz absoluta del proyecto.
- No modificar archivos fuera de esta carpeta sin autorización explícita.
- Mantener un solo proyecto Laravel; no introducir React, Vue, Flutter,
  Firebase, Google Maps ni servicios externos de pago.
- Mantener nombres de dominio, tablas, campos y comentarios en español cuando
  sea razonable. Las clases Eloquent usan singular PascalCase.
- El frontend debe seguir siendo Blade + Bootstrap, mobile-first y accesible.
- No implementar productos, pedidos, carrito, mapas, GPS, pagos, zonas,
  horarios ni notificaciones hasta que se soliciten expresamente.
- Reverb y Echo son la infraestructura de tiempo real local. No integrar
  Pusher, Ably u otros servicios hospedados.

## Estructura relevante

- `app/Models`: modelos `Rol`, `Usuario`, `CategoriaNegocio` y `Negocio`.
- `app/Models/SubcategoriaNegocio.php`: subcategorías para clasificar negocios; no son categorías de productos.
- `app/Http/Middleware/VerificarRol.php`: autorización por rol.
- `app/Http/Controllers/Admin`: dashboard y administración de categorías y negocios.
- `app/Http/Requests/Admin`: validaciones del módulo administrativo.
- `resources/views/layouts/app.blade.php`: layout compartido.
- `resources/views/layouts/admin.blade.php`: navegación administrativa responsive.
- `resources/views/admin`: dashboard, categorías y negocios.
- `resources/css/app.css` y `resources/js/app.js`: entradas Vite.
- `database/migrations` y `database/seeders`: esquema y datos iniciales.
- `public/manifest.webmanifest` y `public/service-worker.js`: base PWA.

Categorías y negocios se desactivan lógicamente mediante `activo`; no añadir
eliminación física desde la interfaz administrativa.

## Flujo local

```powershell
composer install
npm.cmd install
php artisan migrate:fresh --seed
npm.cmd run build
```

Usar `npm.cmd` en PowerShell porque la ejecución de `npm.ps1` puede estar
bloqueada. MySQL local usa por defecto la base `delivery_patacamaya` con el
usuario `root` de Laragon; nunca versionar secretos reales en `.env`.

## Validación obligatoria

Antes de entregar cambios PHP o Blade, ejecutar:

```powershell
vendor\bin\pint
php artisan test
npm.cmd run build
```

Para cambios de interfaz, comprobar también móvil y escritorio, y los estados
de carga, vacío, error y éxito que apliquen. No afirmar que una validación pasó
si no fue ejecutada.

## Git

Este proyecto debe tener su propio `.git`. Antes de cualquier operación Git,
comprobar que `git rev-parse --show-toplevel` devuelve exactamente esta raíz.
Nunca operar sobre un repositorio padre accidental en `C:\`.

Un `AGENTS.md` más cercano a un archivo modificado prevalece para ese subárbol.
