@echo off
setlocal enabledelayedexpansion
set BACKUP_DIR=C:\xampp\htdocs\proyecto\backups
set PROJECT_DIR=C:\xampp\htdocs\proyecto
set MYSQL_BIN=C:\xampp\mysql\bin
set DB_NAME=auto
set DB_USER=root
set DB_PASS=

cls
echo =======================================
echo     RESTAURAR COPIA DE SEGURIDAD
echo =======================================
echo.

echo Selecciona la copia a restaurar:
echo.

set COUNT=0
for /f "tokens=*" %%d in ('dir /b /ad "%BACKUP_DIR%" 2^>nul') do (
    set /a COUNT+=1
    echo [!COUNT!] %%d
)

if %COUNT% equ 0 (
    echo No hay copias disponibles.
    pause
    exit /b 1
)

echo.
set /p CHOICE="Introduce el numero: "

set COUNT=0
for /f "tokens=*" %%d in ('dir /b /ad "%BACKUP_DIR%" 2^>nul') do (
    set /a COUNT+=1
    if !COUNT!==%CHOICE% (
        set SELECTED_BACKUP=%%d
    )
)

if not defined SELECTED_BACKUP (
    echo Seleccion invalida.
    pause
    exit /b 1
)

echo.
echo Has seleccionado: %SELECTED_BACKUP%
echo.

set /p CONFIRM="Confirmar restauracion? (s/n): "
if /i not "%CONFIRM%"=="s" (
    echo Restauracion cancelada.
    pause
    exit /b 0
)

echo.
echo =======================================
echo INICIANDO RESTAURACION...
echo =======================================

echo Restaurando base de datos...
"%MYSQL_BIN%\mysql.exe" -u %DB_USER% -e "DROP DATABASE IF EXISTS %DB_NAME%;"
"%MYSQL_BIN%\mysql.exe" -u %DB_USER% -e "CREATE DATABASE %DB_NAME%;"
for /f "tokens=*" %%f in ('dir /b "%BACKUP_DIR%\%SELECTED_BACKUP%\db_*.sql" 2^>nul') do (
    "%MYSQL_BIN%\mysql.exe" -u %DB_USER% %DB_NAME% < "%BACKUP_DIR%\%SELECTED_BACKUP%\%%f"
)
echo Base de datos restaurada correctamente

echo Restaurando proyecto...
if exist "%BACKUP_DIR%\%SELECTED_BACKUP%\web_*.zip" (
    for /f "tokens=*" %%z in ('dir /b "%BACKUP_DIR%\%SELECTED_BACKUP%\web_*.zip" 2^>nul') do (
        powershell -Command "Expand-Archive -Path '%BACKUP_DIR%\%SELECTED_BACKUP%\%%z' -DestinationPath '%PROJECT_DIR%' -Force"
    )
    echo Proyecto restaurado correctamente
) else (
    echo AVISO: No se encontró archivo ZIP del proyecto
)

echo.
echo =======================================
echo Restauracion completada!
echo =======================================
pause