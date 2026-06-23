@echo off
title KBB Forms - Google Forms Kabupaten Bandung Barat
color 0B

echo ============================================
echo   KBB Forms - Google Forms KBB
echo   Pemerintah Kabupaten Bandung Barat
echo ============================================
echo.

:: Start Backend (Laravel API)
echo [1/2] Starting API Server...
start "KBB Forms API" cmd /c "cd /d "%~dp0backend" && php artisan serve --port=8000"
echo   ^> http://localhost:8000 (REST API)
echo.

:: Wait 2 seconds for backend to initialize
timeout /t 2 /nobreak >nul

:: Start Frontend (React SPA)
echo [2/2] Starting Frontend...
start "KBB Forms Frontend" cmd /c "cd /d "%~dp0frontend" && npm run dev"
echo   ^> http://localhost:5173 (Application)
echo.

echo ============================================
echo   Both servers are starting up!
echo.
echo   Open your browser at:
echo   http://localhost:5173
echo.
echo   Login with:
echo   Email:    admin@dinas.com
echo   Password: admin12345
echo ============================================
echo.
echo  Press any key to close both servers...
pause >nul

:: Kill the started processes when user presses a key
taskkill /f /fi "WINDOWTITLE eq KBB Forms API" >nul 2>&1
taskkill /f /fi "WINDOWTITLE eq KBB Forms Frontend" >nul 2>&1

echo Servers stopped.
