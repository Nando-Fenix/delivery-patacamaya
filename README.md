# Delivery Patacamaya

Base técnica del MVP en Laravel 13, Blade, Bootstrap 5, MySQL y PWA.

## Módulos disponibles

- Autenticación por sesión y acceso restringido por rol.
- Dashboard administrativo en `/admin/inicio`.
- Gestión de categorías en `/admin/categorias`: búsqueda, creación, edición y activación lógica.
- Gestión de negocios en `/admin/negocios`: búsqueda, filtros, detalle, aprobación, rechazo y activación lógica.
- Gestión de subcategorías en `/admin/subcategorias` y clasificación múltiple de negocios.
- Navegación administrativa con sidebar en escritorio y barra inferior segura en móvil.

No existe eliminación física desde estos módulos. Los productos, pedidos, mapas
y demás fases posteriores todavía no están implementados.

## Requisitos

- PHP 8.3 o superior y Composer 2
- Node.js 22 y npm
- MySQL 8 (incluido en Laragon)
- Laragon para el dominio local `delivery-patacamaya.test`

## Instalación

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
npm.cmd install
```

Cree una base de datos MySQL llamada `delivery_patacamaya`, revise las
credenciales `DB_*` del `.env` y ejecute:

```powershell
php artisan migrate:fresh --seed
npm.cmd run build
```

Con Laragon iniciado, abra `http://delivery-patacamaya.test`. Como alternativa:

```powershell
php artisan serve
npm.cmd run dev
```

## Accesos de desarrollo

Todos usan la contraseña `Desarrollo123!`:

| Rol | Correo | Ruta esperada |
|---|---|---|
| Administrador | `administrador@delivery.test` | `/admin/inicio` |
| Cliente | `cliente@delivery.test` | `/cliente/inicio` |
| Negocio | `negocio@delivery.test` | `/negocio/inicio` |
| Repartidor | `repartidor@delivery.test` | `/repartidor/inicio` |

Estas credenciales son exclusivamente locales y deben reemplazarse antes de
cualquier despliegue real.

## Comandos de calidad

```powershell
vendor\bin\pint
php artisan test
npm.cmd run build
```

## PWA y tiempo real

El manifest está en `/manifest.webmanifest` y el service worker mínimo en
`/service-worker.js`. Verifique ambos desde DevTools > Application.

Reverb y Echo están configurados para desarrollo local. Cuando se incorporen
eventos en una fase posterior, inicie el servidor con:

```powershell
php artisan reverb:start
```

No hay eventos, canales privados ni flujos de pedidos implementados todavía.
