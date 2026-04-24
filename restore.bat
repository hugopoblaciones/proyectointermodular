@echo off
REM ============================================================
REM  restore.bat
REM  Sistema de restauracion de copias de seguridad
REM  Ejecutar: restore.bat
REM ============================================================

setlocal enabledelayedexpansion

REM ================= CONFIGURACIÓN =================
set BACKUP_DIR=C:\backup_proyecto
set PROJECT_DIR=C:\xampp\htdocs\proyecto
set MYSQL_BIN=C:\xampp\mysql\bin
set DB_NAME=auto
set DB_USER=root
set DB_PASS=

REM ================= MENÚ =================
cls
echo =======================================
echo     RESTAURAR SISTEMA DE BACKUP
echo =======================================
echo.

REM ================= SELECCIONAR COPIA =================
echo Selecciona la copia a restaurar:
echo.

set COUNT=0
for /f "tokens=*" %%d in ('dir /b /ad "%BACKUP_DIR%" 2^>nul') do (
    set /a COUNT+=1
    echo [!COUNT!] %%d
)

if %COUNT% equ 0 (
    echo No hay copias de seguridad disponibles.
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

echo.
echo Has seleccionado: %SELECTED_BACKUP%
echo.

REM ================= CONFIRMAR =================
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

REM ================= RESTAURAR BASE DE DATOS =================
echo Restaurando base de datos...
"%MYSQL_BIN%\mysql.exe" -u %DB_USER% -e "DROP DATABASE IF EXISTS %DB_NAME%;"
"%MYSQL_BIN%\mysql.exe" -u %DB_USER% -e "CREATE DATABASE %DB_NAME%;"
"%MYSQL_BIN%\mysql.exe" -u %DB_USER% %DB_NAME% < "%BACKUP_DIR%\%SELECTED_BACKUP%\db_*.sql"
if %errorlevel% neq 0 (
    echo ERROR: Fallo al restaurar base de datos
    pause
    exit /b 1
)
echo Base de datos restaurada correctamente

REM ================= RESTAURAR PROYECTO =================
echo Restaurando proyecto...
if exist "%PROJECT_DIR%\backup_temp" rd /s /q "%PROJECT_DIR%\backup_temp"
ren "%PROJECT_DIR%" "backup_temp"
mkdir "%PROJECT_DIR%"
powershell -Command "Expand-Archive -Path '%BACKUP_DIR%\%SELECTED_BACKUP%\web_*.zip' -DestinationPath '%PROJECT_DIR%' -Force"
if %errorlevel% neq 0 (
    echo ERROR: Fallo al restaurar proyecto
    rd /s /q "%PROJECT_DIR%"
    ren "%PROJECT_DIR%" "backup_temp"
    pause
    exit /b 1
)
rd /s /q "%PROJECT_DIR%\backup_temp"
echo Proyecto restaurado correctamente

echo.
echo =======================================
echo Restauracion completada con exito!
echo =======================================
echo.
pause