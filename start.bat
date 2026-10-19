@echo off
title KBB Forms - Forms Pemerintah Kabupaten Bandung Barat
color 0B

echo ============================================
echo   KBB Forms - Google Forms KBB
echo   Pemerintah Kabupaten Bandung Barat
echo ============================================
echo.

:: Catatan REA: direktori frontend/ tidak ada di repo ini (Aplikasi utama
:: adalah aplikasi web Laravel + Livewire). Script ini hanya menjalankan backend.
echo [1/1] Starting Laravel Server...
start "KBB Forms App" cmd /c "cd /d "%~dp0backend" && php artisan serve --port=8000"
echo   ^> http://localhost:8000 (Aplikasi + REST API)
echo.

echo ============================================
echo   Server is starting up!
echo.
echo   Open your browser at:
echo   http://localhost:8000
echo.
echo   Login with:
echo   Email:    admin@dinas.com
echo   Password: admin12345
echo ============================================
echo.
echo  Press any key to stop the server...
pause >nul

taskkill /f /fi "WINDOWTITLE eq KBB Forms App" >nul 2>&1

echo Server stopped.
