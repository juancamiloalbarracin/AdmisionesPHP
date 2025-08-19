@echo off
echo =====================================================
echo  INSTALACION AUTOMATICA - SISTEMA ADMISIONES UDC
echo =====================================================
echo.

REM Verificar si PHP está instalado
php --version >nul 2>&1
if %errorlevel% equ 0 (
    echo [OK] PHP ya esta instalado
    php --version
    goto :check_composer
)

echo [INFO] PHP no encontrado. Verificando XAMPP...

REM Buscar XAMPP en ubicaciones comunes
set "XAMPP_PATH="
if exist "C:\xampp\php\php.exe" set "XAMPP_PATH=C:\xampp\php"
if exist "D:\xampp\php\php.exe" set "XAMPP_PATH=D:\xampp\php"
if exist "%USERPROFILE%\xampp\php\php.exe" set "XAMPP_PATH=%USERPROFILE%\xampp\php"

if defined XAMPP_PATH (
    echo [OK] XAMPP encontrado en: %XAMPP_PATH%
    echo [INFO] Agregando PHP al PATH temporalmente...
    set "PATH=%XAMPP_PATH%;%PATH%"
    php --version
    goto :check_composer
)

echo [ERROR] No se encontro PHP ni XAMPP instalado
echo.
echo OPCIONES DE INSTALACION:
echo 1. Instalar XAMPP (recomendado para desarrollo)
echo    - Descargar desde: https://www.apachefriends.org/download.html
echo    - Instalar con Apache, MySQL y PHP
echo.
echo 2. Instalar PHP standalone
echo    - Descargar desde: https://windows.php.net/download
echo    - Agregar al PATH del sistema
echo.
echo Despues de instalar, ejecuta este script nuevamente.
pause
exit /b 1

:check_composer
echo.
echo [INFO] Verificando Composer...
composer --version >nul 2>&1
if %errorlevel% equ 0 (
    echo [OK] Composer ya esta instalado
    goto :install_dependencies
)

echo [ERROR] Composer no encontrado
echo [INFO] Descargando e instalando Composer...

REM Descargar Composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
if %errorlevel% neq 0 (
    echo [ERROR] No se pudo descargar Composer
    echo [INFO] Instala Composer manualmente desde: https://getcomposer.org/
    pause
    exit /b 1
)

REM Instalar Composer
php composer-setup.php
if %errorlevel% neq 0 (
    echo [ERROR] Error instalando Composer
    pause
    exit /b 1
)

REM Limpiar archivo temporal
php -r "unlink('composer-setup.php');"

echo [OK] Composer instalado exitosamente

:install_dependencies
echo.
echo [INFO] Instalando dependencias de PHP...
php composer.phar install --no-dev --optimize-autoloader
if %errorlevel% neq 0 (
    echo [ERROR] Error instalando dependencias
    pause
    exit /b 1
)

echo [OK] Dependencias instaladas

:check_mysql
echo.
echo [INFO] Verificando MySQL...
mysql --version >nul 2>&1
if %errorlevel% equ 0 (
    echo [OK] MySQL ya esta instalado
    goto :setup_complete
)

REM Verificar MySQL en XAMPP
if defined XAMPP_PATH (
    set "MYSQL_PATH=%XAMPP_PATH:php=mysql\bin%"
    if exist "%MYSQL_PATH%\mysql.exe" (
        echo [OK] MySQL encontrado en XAMPP: %MYSQL_PATH%
        set "PATH=%MYSQL_PATH%;%PATH%"
        goto :setup_complete
    )
)

echo [WARNING] MySQL no encontrado
echo [INFO] Si usas XAMPP, asegurate de iniciar el servicio MySQL
echo [INFO] Si no tienes MySQL, puedes:
echo   1. Usar XAMPP (incluye MySQL)
echo   2. Instalar MySQL Server desde: https://dev.mysql.com/downloads/mysql/

:setup_complete
echo.
echo =====================================================
echo             INSTALACION COMPLETADA
echo =====================================================
echo.
echo [OK] PHP instalado y funcionando
echo [OK] Composer instalado
echo [OK] Dependencias de PHP instaladas
echo.
echo SIGUIENTES PASOS:
echo 1. Configurar archivo .env con datos de tu BD
echo 2. Ejecutar: php check-requirements.php
echo 3. Ejecutar: php migrations\001_create_tables.php
echo 4. Iniciar servidor: php -S localhost:8080
echo.
echo =====================================================
pause
