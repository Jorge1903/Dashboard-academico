@echo off
cd /d "%~dp0backend"
echo Aplicacion: http://127.0.0.1:8000
echo Mantenga Metabase activo en http://localhost:3000
php artisan serve --host=127.0.0.1 --port=8000 --no-reload
