Write-Host "=========================================" -ForegroundColor Cyan
Write-Host " DELIVERY PATACAMAYA - INSTALACION" -ForegroundColor Cyan
Write-Host "=========================================" -ForegroundColor Cyan

$ErrorActionPreference = "Stop"

# ------------------------------------------------------------
# CONFIGURACION MYSQL
# ------------------------------------------------------------

Write-Host ""
Write-Host "Configuracion de MySQL" -ForegroundColor Yellow

$dbHost = Read-Host "Host MySQL [127.0.0.1]"
if ([string]::IsNullOrWhiteSpace($dbHost)) {
    $dbHost = "127.0.0.1"
}

$dbPort = Read-Host "Puerto MySQL [3306]"
if ([string]::IsNullOrWhiteSpace($dbPort)) {
    $dbPort = "3306"
}

$dbName = Read-Host "Nombre de la base de datos [delivery_patacamaya]"
if ([string]::IsNullOrWhiteSpace($dbName)) {
    $dbName = "delivery_patacamaya"
}

$dbUser = Read-Host "Usuario MySQL [root]"
if ([string]::IsNullOrWhiteSpace($dbUser)) {
    $dbUser = "root"
}

$dbPasswordSecure = Read-Host "Password MySQL (ENTER si no tiene)" -AsSecureString
$dbPasswordPtr = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($dbPasswordSecure)
$dbPassword = [Runtime.InteropServices.Marshal]::PtrToStringBSTR($dbPasswordPtr)

# ------------------------------------------------------------
# COMPROBAR PHP
# ------------------------------------------------------------

Write-Host ""
Write-Host "[1/10] Verificando PHP..." -ForegroundColor Yellow

php --version

if ($LASTEXITCODE -ne 0) {
    Write-Host "ERROR: PHP no esta disponible." -ForegroundColor Red
    exit 1
}

# ------------------------------------------------------------
# COMPROBAR COMPOSER
# ------------------------------------------------------------

Write-Host ""
Write-Host "[2/10] Instalando dependencias PHP..." -ForegroundColor Yellow

composer install

if ($LASTEXITCODE -ne 0) {
    Write-Host "ERROR durante composer install." -ForegroundColor Red
    exit 1
}

# ------------------------------------------------------------
# NODE
# ------------------------------------------------------------

Write-Host ""
Write-Host "[3/10] Instalando dependencias JavaScript..." -ForegroundColor Yellow

npm install

if ($LASTEXITCODE -ne 0) {
    Write-Host "ERROR durante npm install." -ForegroundColor Red
    exit 1
}

# ------------------------------------------------------------
# ENV
# ------------------------------------------------------------

Write-Host ""
Write-Host "[4/10] Configurando .env..." -ForegroundColor Yellow

if (-not (Test-Path ".env")) {
    Copy-Item ".env.example" ".env"
    Write-Host ".env creado." -ForegroundColor Green
}
else {
    Write-Host ".env ya existe. Se actualizara la configuracion de BD." -ForegroundColor Green
}

# ------------------------------------------------------------
# ACTUALIZAR VARIABLES DE BD EN .ENV
# ------------------------------------------------------------

$envContent = Get-Content ".env" -Raw

function Set-EnvValue {
    param (
        [string]$Content,
        [string]$Key,
        [string]$Value
    )

    if ($Content -match "(?m)^$Key=") {
        return [regex]::Replace(
            $Content,
            "(?m)^$Key=.*$",
            "$Key=$Value"
        )
    }
    else {
        return $Content + "`r`n$Key=$Value"
    }
}

$envContent = Set-EnvValue $envContent "DB_CONNECTION" "mysql"
$envContent = Set-EnvValue $envContent "DB_HOST" $dbHost
$envContent = Set-EnvValue $envContent "DB_PORT" $dbPort
$envContent = Set-EnvValue $envContent "DB_DATABASE" $dbName
$envContent = Set-EnvValue $envContent "DB_USERNAME" $dbUser
$envContent = Set-EnvValue $envContent "DB_PASSWORD" $dbPassword

Set-Content ".env" $envContent -Encoding UTF8

Write-Host "Configuracion de base de datos escrita en .env." -ForegroundColor Green

# ------------------------------------------------------------
# CREAR BASE DE DATOS
# ------------------------------------------------------------

Write-Host ""
Write-Host "[5/10] Creando base de datos..." -ForegroundColor Yellow

$mysqlArgs = @(
    "-h", $dbHost,
    "-P", $dbPort,
    "-u", $dbUser
)

if (-not [string]::IsNullOrWhiteSpace($dbPassword)) {
    $mysqlArgs += "-p$dbPassword"
}

$createDatabaseSql = "CREATE DATABASE IF NOT EXISTS ``$dbName`` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

$createDatabaseSql | mysql @mysqlArgs

if ($LASTEXITCODE -ne 0) {
    Write-Host ""
    Write-Host "ERROR: no se pudo crear la base de datos." -ForegroundColor Red
    Write-Host "Verifica que MySQL este iniciado y que usuario/password sean correctos." -ForegroundColor Yellow
    exit 1
}

Write-Host "Base de datos '$dbName' lista." -ForegroundColor Green

# ------------------------------------------------------------
# APP KEY
# ------------------------------------------------------------

Write-Host ""
Write-Host "[6/10] Configurando Laravel..." -ForegroundColor Yellow

php artisan key:generate --force
php artisan optimize:clear

# ------------------------------------------------------------
# MIGRACIONES
# ------------------------------------------------------------

Write-Host ""
Write-Host "[7/10] Ejecutando migraciones..." -ForegroundColor Yellow

php artisan migrate --force

if ($LASTEXITCODE -ne 0) {
    Write-Host "ERROR ejecutando migraciones." -ForegroundColor Red
    exit 1
}

# ------------------------------------------------------------
# STORAGE LINK
# ------------------------------------------------------------

Write-Host ""
Write-Host "[8/10] Configurando storage..." -ForegroundColor Yellow

php artisan storage:link 2>$null

# ------------------------------------------------------------
# BUILD
# ------------------------------------------------------------

Write-Host ""
Write-Host "[9/10] Compilando frontend..." -ForegroundColor Yellow

npm run build

if ($LASTEXITCODE -ne 0) {
    Write-Host "ERROR compilando frontend." -ForegroundColor Red
    exit 1
}

# ------------------------------------------------------------
# TESTS
# ------------------------------------------------------------

Write-Host ""
Write-Host "[10/10] Ejecutando pruebas..." -ForegroundColor Yellow

php artisan test

if ($LASTEXITCODE -ne 0) {
    Write-Host ""
    Write-Host "La instalacion termino, pero existen pruebas fallidas." -ForegroundColor Yellow
}
else {
    Write-Host "Todas las pruebas aprobaron." -ForegroundColor Green
}

# ------------------------------------------------------------
# FINAL
# ------------------------------------------------------------

Write-Host ""
Write-Host "=========================================" -ForegroundColor Green
Write-Host " DELIVERY PATACAMAYA INSTALADO" -ForegroundColor Green
Write-Host "=========================================" -ForegroundColor Green

Write-Host ""
Write-Host "Base de datos: $dbName"
Write-Host "MySQL: $dbHost`:$dbPort"
Write-Host ""

Write-Host "Para iniciar el entorno de desarrollo:" -ForegroundColor Cyan
Write-Host ".\iniciar-desarrollo.ps1"
Write-Host ""

Write-Host "O manualmente:" -ForegroundColor Cyan
Write-Host "Terminal 1: php artisan serve"
Write-Host "Terminal 2: php artisan reverb:start --host=127.0.0.1 --port=8080 --debug"
Write-Host "Terminal 3: php artisan queue:work --verbose"
Write-Host "Terminal 4: npm run dev"