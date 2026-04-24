@echo off
REM ============================================================
REM  backup.bat
REM  Sistema automatico de copias de seguridad
REM  Ejecutar: backup.bat
REM ============================================================

setlocal

REM ================= CONFIGURACIÓN =================
set BACKUP_DIR=C:\backup_proyecto
set PROJECT_DIR=C:\xampp\htdocs\proyecto
set MYSQL_BIN=C:\xampp\mysql\bin
set DB_NAME=auto
set DB_USER=root
set DB_PASS=
set RETENTION_DAYS=7

REM ================= FECHA Y HORA =================
for /f "tokens=1-4 delims=/ " %%a in ('date /t') do (
    set FECHA=%%c%%a%%b
)
for /f "tokens=1-2 delims=: " %%a in ('time /t') do (
    set HORA=%%a%%b
)
set TIMESTAMP=%FECHA%_%HORA%
set TIMESTAMP=%TIMESTAMP: =%

REM ================= CREAR CARPETAS =================
if not exist "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"
if not exist "%BACKUP_DIR%\%TIMESTAMP%" mkdir "%BACKUP_DIR%\%TIMESTAMP%"

REM ================= LOG =================
echo ======================================= >> "%BACKUP_DIR%\backup.log"
echo Backup iniciado: %date% %time% >> "%BACKUP_DIR%\backup.log"

REM ================= COPIA BASE DE DATOS =================
echo Generando copia de base de datos...
"%MYSQL_BIN%\mysqldump.exe" -u %DB_USER% %DB_NAME% > "%BACKUP_DIR%\%TIMESTAMP%\db_%TIMESTAMP%.sql"
if %errorlevel% neq 0 (
    echo ERROR: Fallo al copiar base de datos >> "%BACKUP_DIR%\backup.log"
    echo ERROR: db backup failed >> "%BACKUP_DIR%\%TIMESTAMP%\error.log"
    exit /b 1
)
echo Base de datos copiada correctamente >> "%BACKUP_DIR%\backup.log"

REM ================= COPIA PROYECTO (ZIP) =================
echo Generando copia del proyecto...
powershell -Command "Compress-Archive -Path '%PROJECT_DIR%\*' -DestinationPath '%BACKUP_DIR%\%TIMESTAMP%\web_%TIMESTAMP%.zip' -Force"
if %errorlevel% neq 0 (
    echo ERROR: Fallo al comprimir proyecto >> "%BACKUP_DIR%\backup.log"
    echo ERROR: web backup failed >> "%BACKUP_DIR%\%TIMESTAMP%\error.log"
    exit /b 1
)
echo Proyecto copiado correctamente >> "%BACKUP_DIR%\backup.log"

REM ================= LIMPIEZA DE COPIAS ANTIGUAS =================
echo Limpiando copias anciennes...
forfiles /p "%BACKUP_DIR%" /d -%RETENTION_DAYS% /c "cmd /c if @isdir==TRUE rd /s /q @path" 2>nul
echo Limpieza completada >> "%BACKUP_DIR%\backup.log"

REM ================= FIN =================
echo Backup completado: %date% %time% >> "%BACKUP_DIR%\backup.log"
echo.
echo =======================================
echo Backup completado con exito!
echo Ubicacion: %BACKUP_DIR%\%TIMESTAMP%
echo =======================================
echo.
pause