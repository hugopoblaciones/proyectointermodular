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

echo =======================================
echo Backup iniciado: %date% %time%

echo Generando copia de base de datos...
"%MYSQL_BIN%\mysqldump.exe" -u %DB_USER% %DB_NAME% > "%BACKUP_DIR%\%TIMESTAMP%\db_%TIMESTAMP%.sql"
if %errorlevel% neq 0 (
    echo ERROR: Fallo al copiar base de datos
    exit /b 1
)
echo Base de datos copiada correctamente

echo Generando copia del proyecto...
powershell -Command "Get-ChildItem -Path '%PROJECT_DIR%\*' -Recurse | Where-Object { -not $_.PSIsContainer } | ForEach-Object { $_.FullName } | Compress-Archive -DestinationPath '%BACKUP_DIR%\%TIMESTAMP%\web_%TIMESTAMP%.zip' -CompressionLevel Optimal -Force"
if %errorlevel% neq 0 (
    echo ERROR: Fallo al comprimir proyecto
    exit /b 1
)
echo Proyecto copiado correctamente

echo Limpiando copias antiguas...
forfiles /p "%BACKUP_DIR%" /d -%RETENTION_DAYS% /c "cmd /c if @isdir==TRUE rd /s /q @path" 2>nul

echo.
echo =======================================
echo Backup completado con exito!
echo Ubicacion: %BACKUP_DIR%\%TIMESTAMP%
echo =======================================
pause