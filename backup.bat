@echo off
setlocal
set BACKUP_DIR=C:\xampp\htdocs\proyecto\backups
set PROJECT_DIR=C:\xampp\htdocs\proyecto
set MYSQL_BIN=C:\xampp\mysql\bin
set DB_NAME=auto
set DB_USER=root
set DB_PASS=
set RETENTION_DAYS=7

for /f "tokens=1-4 delims=/ " %%a in ('date /t') do set FECHA=%%c%%a%%b
for /f "tokens=1-2 delims=: " %%a in ('time /t') do set HORA=%%a%%b
set TIMESTAMP=%FECHA%_%HORA%
set TIMESTAMP=%TIMESTAMP: =%

if not exist "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"
if not exist "%BACKUP_DIR%\%TIMESTAMP%" mkdir "%BACKUP_DIR%\%TIMESTAMP%"

set LOG_FILE=%BACKUP_DIR%\backup.log

echo =======================================
echo Backup iniciado: %date% %time%
echo [%date% %time%] === Backup iniciado: %TIMESTAMP% === >> "%LOG_FILE%"

echo Generando copia de base de datos...
"%MYSQL_BIN%\mysqldump.exe" -u %DB_USER% %DB_NAME% > "%BACKUP_DIR%\%TIMESTAMP%\db_%TIMESTAMP%.sql"
if %errorlevel% neq 0 (
    echo ERROR: Fallo al copiar base de datos
    echo [%date% %time%] [ERROR] Fallo al generar dump SQL >> "%LOG_FILE%"
    exit /b 1
)
echo Base de datos copiada correctamente
echo [%date% %time%] [OK] Base de datos copiada: db_%TIMESTAMP%.sql >> "%LOG_FILE%"

echo Generando copia del proyecto...
powershell -Command "Get-ChildItem -Path '%PROJECT_DIR%' -Recurse | Where-Object { $_.FullName -notlike '*\backups\*' -and -not $_.PSIsContainer } | Compress-Archive -DestinationPath '%BACKUP_DIR%\%TIMESTAMP%\web_%TIMESTAMP%.zip' -CompressionLevel Optimal -Force"
if %errorlevel% neq 0 (
    echo ERROR: Fallo al comprimir proyecto
    echo [%date% %time%] [ERROR] Fallo al comprimir ZIP del proyecto >> "%LOG_FILE%"
    exit /b 1
)
echo Proyecto copiado correctamente
echo [%date% %time%] [OK] Proyecto comprimido: web_%TIMESTAMP%.zip >> "%LOG_FILE%"

echo Limpiando copias antiguas...
forfiles /p "%BACKUP_DIR%" /d -%RETENTION_DAYS% /c "cmd /c if @isdir==TRUE rd /s /q @path" 2>nul
echo [%date% %time%] [OK] Limpieza ejecutada (retencion: %RETENTION_DAYS% dias) >> "%LOG_FILE%"

echo.
echo =======================================
echo Backup completado con exito!
echo Ubicacion: %BACKUP_DIR%\%TIMESTAMP%
echo =======================================
echo [%date% %time%] === Backup completado: %TIMESTAMP% === >> "%LOG_FILE%"
