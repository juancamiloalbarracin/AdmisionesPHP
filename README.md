# Sistema de Admisiones UDC - Backend PHP

##  Descripción
Backend en PHP para el Sistema de Admisiones de la Universidad de Córdoba. Este proyecto es una migración del sistema Java + MongoDB a PHP + MySQL, manteniendo exactamente las mismas funcionalidades.

##  Arquitectura
```
Frontend React  Backend PHP  MySQL Database
```

### Tecnologías Utilizadas
- **PHP 8.1+** con programación orientada a objetos
- **MySQL 8.0** como base de datos principal
- **JWT** para autenticación
- **Composer** para gestión de dependencias
- **PDO** para conexión segura a base de datos

##  Funcionalidades
-  Autenticación con JWT
-  Registro y gestión de usuarios
-  Información personal completa
-  Información académica
-  Sistema de solicitudes con radicación
-  Dashboard consolidado
-  APIs REST compatibles con React frontend

##  Instalación

### Requisitos Previos
- PHP 8.1 o superior
- MySQL 8.0 o superior
- Composer
- Apache/Nginx con mod_rewrite habilitado

### Pasos de Instalación

1. **Clonar el repositorio**
   ```bash
   git clone [repo-url]
   cd proyecto-PHP
   ```

2. **Instalar dependencias**
   ```bash
   composer install
   ```

3. **Configurar variables de entorno**
   ```bash
   cp .env.example .env
   # Editar .env con la configuración de tu base de datos
   ```

4. **Crear base de datos y ejecutar migraciones**
   ```bash
   php migrations/001_create_tables.php
   ```

5. **Configurar servidor web**
   - Apuntar document root a la carpeta del proyecto
   - Asegurar que mod_rewrite esté habilitado
   - Verificar que .htaccess sea procesado

##  Configuración

### Variables de Entorno
Configurar el archivo `.env` con los siguientes valores:

```env
# Base de datos
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=admisiones_udc
DB_USERNAME=root
DB_PASSWORD=tu_password

# JWT
JWT_SECRET_KEY=tu_clave_secreta_muy_segura
JWT_EXPIRATION_TIME=86400

# Aplicación
APP_ENV=development
APP_DEBUG=true
```

### Estructura del Proyecto
```
proyecto-PHP/
 config/          # Archivos de configuración
 src/
    controllers/ # Controladores de API
    models/      # Modelos de datos
    middleware/  # Middlewares (autenticación, CORS)
    utils/       # Utilidades (Database, JWT)
 api/             # Endpoints REST
 migrations/      # Scripts de migración de BD
 tests/          # Pruebas unitarias
 vendor/         # Dependencias de Composer
 .htaccess       # Configuración de Apache
```

##  API Endpoints

### Autenticación
- `POST /api/auth/login` - Iniciar sesión
- `POST /api/auth/logout` - Cerrar sesión
- `POST /api/auth/validate` - Validar token

### Usuarios
- `POST /api/users/register` - Registrar usuario
- `GET /api/users/profile` - Obtener perfil

### Información Personal
- `GET /api/info-personal/get` - Obtener información personal
- `POST /api/info-personal/save` - Guardar información personal

### Información Académica
- `GET /api/info-academica/get` - Obtener información académica
- `POST /api/info-academica/save` - Guardar información académica

### Solicitudes
- `POST /api/solicitudes/radicar` - Radicar nueva solicitud
- `GET /api/solicitudes/list` - Listar solicitudes del usuario

##  Testing

### Ejecutar Pruebas
```bash
composer test
```

### Verificar Estilo de Código
```bash
composer cs-check
```

### Corregir Estilo de Código
```bash
composer cs-fix
```

##  Despliegue

### Desarrollo
1. Usar servidor PHP integrado:
   ```bash
   php -S localhost:8080
   ```

### Producción
1. Configurar Apache/Nginx
2. Establecer `APP_ENV=production` en `.env`
3. Optimizar Composer: `composer install --no-dev --optimize-autoloader`
4. Configurar SSL/HTTPS
5. Configurar logs y monitoreo

##  Mantenimiento

### Logs
Los logs se almacenan en:
- `logs/admisiones.log` - Log principal de la aplicación
- `logs/database.log` - Log de consultas de base de datos
- `logs/php_errors.log` - Errores de PHP

### Base de Datos
- Hacer respaldos regulares de MySQL
- Monitorear consultas lentas
- Mantener índices optimizados

##  Desarrollo

### Estándares de Código
- PSR-12 para estilo de código
- Comentarios en español para funciones públicas
- Tipado estricto en PHP
- Validación de datos de entrada

### Git Workflow
1. Crear rama desde `master`
2. Desarrollar funcionalidad
3. Ejecutar tests
4. Crear Pull Request
5. Review y merge

##  Migración desde Java

Este proyecto reemplaza el sistema Java + MongoDB manteniendo:
-  Misma funcionalidad exacta
-  Mismas APIs REST
-  Compatibilidad con frontend React
-  Mismo esquema de datos (adaptado a MySQL)

##  Soporte

Para soporte técnico contactar:
- Email: admisiones@unicordoba.edu.co
- Documentación: Ver carpeta `/docs`

---

**Universidad de Córdoba - Sistema de Admisiones 2025**
