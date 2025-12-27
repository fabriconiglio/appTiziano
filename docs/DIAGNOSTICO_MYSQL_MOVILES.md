# Diagnóstico y Solución: Error de Conexión MySQL en Móviles

## Problema

El sistema muestra error `Connection refused` al intentar conectarse a MySQL cuando se accede desde dispositivos móviles, pero funciona correctamente desde escritorio.

## Cambios Implementados

### 1. Configuración de Cookies SameSite (`config/session.php`)

Se agregó documentación sobre la configuración de cookies SameSite para mejorar la compatibilidad con navegadores móviles.

**Nota**: La configuración actual usa `SESSION_SAME_SITE=lax` que debería funcionar bien. Si el problema persiste específicamente en Safari iOS, considera cambiar a `none` con `secure=true` en el archivo `.env`:

```env
SESSION_SAME_SITE=none
SESSION_SECURE_COOKIE=true
```

### 2. Timeouts de Conexión MySQL (`config/database.php`)

Se agregaron las siguientes mejoras:

- **Timeout de conexión aumentado**: 30 segundos (configurable con `DB_CONNECT_TIMEOUT`)
- **Reconexión automática**: Opcional mediante `DB_PERSISTENT`
- **Comando de inicialización**: Para mejorar estabilidad en conexiones móviles

**Variables de entorno disponibles**:
```env
DB_CONNECT_TIMEOUT=30
DB_PERSISTENT=false
```

### 3. Manejo de Errores Mejorado (`bootstrap/app.php`)

Se implementó captura específica de errores de conexión MySQL que:

- Registra errores detallados en los logs de Laravel
- Muestra una página de error amigable en producción
- Incluye información de diagnóstico (URL, User-Agent, IP)

### 4. Vista de Error Personalizada (`resources/views/errors/database-connection.blade.php`)

Se creó una vista de error amigable que se muestra cuando hay problemas de conexión a la base de datos.

## Pasos de Diagnóstico en el Servidor

### Paso 1: Ejecutar Script de Diagnóstico

Conectarse al servidor y ejecutar:

```bash
ssh root@165.227.202.245
cd /ruta/a/tu/aplicacion/laravel
bash scripts/diagnose_mysql_connection.sh
```

El script verificará:
- Estado de MySQL/MariaDB
- Conexión con las credenciales
- Existencia de la tabla `sessions`
- Configuración de bind-address
- Logs recientes de MySQL
- Permisos del usuario
- Variables de entorno de Laravel
- Puerto MySQL

### Paso 2: Verificar Estado de MySQL Manualmente

```bash
# Verificar si MySQL está corriendo
systemctl status mysql
# o
systemctl status mariadb

# Si no está corriendo, iniciarlo
systemctl start mysql
# o
systemctl start mariadb
```

### Paso 3: Probar Conexión MySQL

```bash
mysql -u laravel_user -p laravel_admin
# Contraseña: Tiziano2025!Laravel

# Una vez conectado, verificar tabla sessions
SHOW TABLES LIKE 'sessions';

# Si no existe, crear la tabla
exit
php artisan session:table
php artisan migrate
```

### Paso 4: Verificar Configuración de MySQL

```bash
# Ver configuración de bind-address
mysql -u laravel_user -p'Tiziano2025!Laravel' -e "SHOW VARIABLES LIKE 'bind_address';"

# Ver puerto MySQL
mysql -u laravel_user -p'Tiziano2025!Laravel' -e "SHOW VARIABLES LIKE 'port';"
```

**Importante**: Si `bind_address` está configurado como `127.0.0.1` o `localhost`, MySQL solo acepta conexiones locales. Esto debería estar bien si PHP y MySQL están en el mismo servidor.

### Paso 5: Verificar Variables de Entorno

```bash
# Ver configuración de base de datos en .env
grep "^DB_" .env

# Verificar que coincidan con las credenciales reales:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1 (o localhost)
# DB_PORT=3306
# DB_DATABASE=laravel_admin
# DB_USERNAME=laravel_user
# DB_PASSWORD=Tiziano2025!Laravel
```

### Paso 6: Limpiar Caché de Laravel

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Si estás en producción, regenerar caché optimizado
php artisan config:cache
```

### Paso 7: Verificar Logs de Laravel

```bash
# Ver últimos errores
tail -n 50 storage/logs/laravel.log

# Monitorear en tiempo real
tail -f storage/logs/laravel.log
```

## Soluciones Adicionales

### Solución Temporal: Cambiar Driver de Sesiones a 'file'

Si el problema persiste y necesitas una solución rápida, puedes cambiar temporalmente el driver de sesiones:

```bash
# Editar .env
nano .env

# Cambiar:
SESSION_DRIVER=database
# Por:
SESSION_DRIVER=file

# Limpiar caché
php artisan config:clear
```

**Nota**: Las sesiones en archivo no escalan bien en múltiples servidores, pero pueden resolver el problema temporalmente.

### Verificar Permisos de Storage

Si cambias a sesiones en archivo, asegúrate de que el directorio tenga permisos correctos:

```bash
chmod -R 775 storage/framework/sessions
chown -R www-data:www-data storage/framework/sessions
# Ajusta www-data según tu usuario de servidor web
```

## Verificación Post-Cambios

Después de aplicar los cambios:

1. **Limpiar caché de configuración**:
   ```bash
   php artisan config:clear
   ```

2. **Probar desde escritorio**: Verificar que sigue funcionando correctamente

3. **Probar desde móvil**: Acceder desde un dispositivo móvil y verificar que el error ya no aparece

4. **Monitorear logs**: Revisar `storage/logs/laravel.log` para ver si hay nuevos errores

## Posibles Causas del Problema

1. **MySQL no está corriendo**: El servicio MySQL se detuvo
2. **Tabla sessions no existe**: La migración de sesiones no se ejecutó
3. **Credenciales incorrectas**: Las variables de entorno no coinciden con las credenciales reales
4. **Problemas de red**: Firewall bloqueando conexiones desde ciertas IPs
5. **Timeouts cortos**: Las conexiones desde móviles tardan más y exceden el timeout
6. **Cookies SameSite**: Navegadores móviles más estrictos con políticas de cookies

## Contacto

Si el problema persiste después de seguir estos pasos, revisa los logs detallados y contacta al administrador del sistema.

