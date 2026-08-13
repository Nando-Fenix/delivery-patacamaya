Write-Host "Iniciando Delivery Patacamaya..." -ForegroundColor Cyan

Start-Process powershell -ArgumentList "-NoExit", "-Command", "php artisan serve"
Start-Sleep -Seconds 1

Start-Process powershell -ArgumentList "-NoExit", "-Command", "php artisan reverb:start --host=127.0.0.1 --port=8080 --debug"
Start-Sleep -Seconds 1

Start-Process powershell -ArgumentList "-NoExit", "-Command", "php artisan queue:work --verbose"
Start-Sleep -Seconds 1

Start-Process powershell -ArgumentList "-NoExit", "-Command", "npm run dev"

Write-Host ""
Write-Host "Procesos iniciados:" -ForegroundColor Green
Write-Host "- Laravel"
Write-Host "- Reverb"
Write-Host "- Queue Worker"
Write-Host "- Vite"
Write-Host ""
Write-Host "Abre: http://127.0.0.1:8000"