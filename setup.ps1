Write-Host "=========================================" -ForegroundColor Cyan
Write-Host " Delivery Patacamaya - Instalacion" -ForegroundColor Cyan
Write-Host "=========================================" -ForegroundColor Cyan

# Comprobar PHP
Write-Host "`n[1/8] Verificando PHP..." -ForegroundColor Yellow
php --version
if ($LASTEXITCODE -ne 0) {
    Write-Host "ERROR: PHP no esta disponible." -ForegroundColor Red
    exit 1
}

# Comprobar Composer
Write-Host "`n[2/8] Instalando dependencias PHP..." -ForegroundColor Yellow
composer install
if ($LASTEXITCODE -ne 0) {
    Write-Host "ERROR durante composer install." -ForegroundColor Red
    exit 1
}

# Dependencias frontend
Write-Host "`n[3/8] Instalando dependencias JavaScript..." -ForegroundColor Yellow
npm install
if ($LASTEXITCODE -ne 0) {
    Write-Host "ERROR durante npm install." -ForegroundColor Red
    exit 1
}

# Crear .env
if (-not (Test-Path ".env")) {
    Write-Host "`n[4/8] Creando .env desde .env.example..." -ForegroundColor Yellow
    Copy-Item ".env.example" ".env"

    Write-Host ""
    Write-Host "Se creo .env." -ForegroundColor Green
    Write-Host "IMPORTANTE: configura los datos de MySQL antes de continuar." -ForegroundColor Yellow
    Write-Host ""
    Read-Host "Presiona ENTER cuando hayas configurado .env"
}
else {
    Write-Host "`n[4/8] .env ya existe. Se conservara." -ForegroundColor Green
}

# Generar APP_KEY solamente si hace falta
Write-Host "`n[5/8] Configurando Laravel..." -ForegroundColor Yellow
php artisan key:generate --force

php artisan optimize:clear

# Migraciones
Write-Host "`n[6/8] Ejecutando migraciones..." -ForegroundColor Yellow
php artisan migrate

if ($LASTEXITCODE -ne 0) {
    Write-Host ""
    Write-Host "ERROR al ejecutar migraciones." -ForegroundColor Red
    Write-Host "Verifica que MySQL este iniciado y que .env tenga la base de datos correcta." -ForegroundColor Yellow
    exit 1
}

# Storage
Write-Host "`n[7/8] Configurando storage..." -ForegroundColor Yellow
php artisan storage:link 2>$null

# Compilar frontend
Write-Host "`n[8/8] Compilando frontend..." -ForegroundColor Yellow
npm run build

Write-Host ""
Write-Host "=========================================" -ForegroundColor Green
Write-Host " Instalacion completada" -ForegroundColor Green
Write-Host "=========================================" -ForegroundColor Green

Write-Host ""
Write-Host "Para desarrollar el proyecto necesitas:" -ForegroundColor Cyan
Write-Host ""
Write-Host "Terminal 1: php artisan serve"
Write-Host "Terminal 2: php artisan reverb:start --host=127.0.0.1 --port=8080 --debug"
Write-Host "Terminal 3: php artisan queue:work --verbose"
Write-Host "Terminal 4: npm run dev"
Write-Host ""
Write-Host "Aplicacion: http://127.0.0.1:8000" -ForegroundColor Cyan