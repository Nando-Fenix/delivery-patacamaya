$proyecto = "C:\laragon\www\delivery-patacamaya"
$php = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe"

Write-Host "=========================================" -ForegroundColor Cyan
Write-Host " DELIVERY PATACAMAYA" -ForegroundColor Cyan
Write-Host "=========================================" -ForegroundColor Cyan

$version = & $php -r "echo PHP_VERSION;"

Write-Host ""
Write-Host "PHP utilizado: $version" -ForegroundColor Green
Write-Host ""

Start-Process powershell `
    -WorkingDirectory $proyecto `
    -ArgumentList "-NoExit", "-Command", "& '$php' artisan serve"

Start-Sleep -Seconds 1

Start-Process powershell `
    -WorkingDirectory $proyecto `
    -ArgumentList "-NoExit", "-Command", "& '$php' artisan reverb:start --host=127.0.0.1 --port=8080 --debug"

Start-Sleep -Seconds 1

Start-Process powershell `
    -WorkingDirectory $proyecto `
    -ArgumentList "-NoExit", "-Command", "& '$php' artisan queue:work --verbose"

Start-Sleep -Seconds 1

Start-Process powershell `
    -WorkingDirectory $proyecto `
    -ArgumentList "-NoExit", "-Command", "npm.cmd run dev"

Write-Host ""
Write-Host "Entorno iniciado correctamente." -ForegroundColor Green
Write-Host "Laravel: http://127.0.0.1:8000"
Write-Host "Reverb: 127.0.0.1:8080"