#!/bin/bash

# Script de diagnóstico para problemas de conexión MySQL en móviles
# Ejecutar en el servidor: bash scripts/diagnose_mysql_connection.sh

echo "=========================================="
echo "Diagnóstico de Conexión MySQL"
echo "=========================================="
echo ""

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 1. Verificar estado de MySQL
echo "1. Verificando estado de MySQL..."
if systemctl is-active --quiet mysql || systemctl is-active --quiet mariadb; then
    echo -e "${GREEN}✓ MySQL/MariaDB está corriendo${NC}"
    systemctl status mysql --no-pager -l || systemctl status mariadb --no-pager -l
else
    echo -e "${RED}✗ MySQL/MariaDB NO está corriendo${NC}"
    echo "Intentando iniciar MySQL..."
    systemctl start mysql || systemctl start mariadb
fi
echo ""

# 2. Verificar conexión MySQL
echo "2. Verificando conexión MySQL..."
echo "Intentando conectar con: mysql -u laravel_user -p laravel_admin"
echo "Si te pide contraseña, usa: Tiziano2025!Laravel"
echo ""
mysql -u laravel_user -p'Tiziano2025!Laravel' laravel_admin -e "SELECT 1;" 2>&1
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Conexión MySQL exitosa${NC}"
else
    echo -e "${RED}✗ Error en conexión MySQL${NC}"
fi
echo ""

# 3. Verificar que la tabla sessions existe
echo "3. Verificando tabla 'sessions'..."
mysql -u laravel_user -p'Tiziano2025!Laravel' laravel_admin -e "SHOW TABLES LIKE 'sessions';" 2>&1 | grep -q "sessions"
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ La tabla 'sessions' existe${NC}"
    mysql -u laravel_user -p'Tiziano2025!Laravel' laravel_admin -e "SELECT COUNT(*) as total_sessions FROM sessions;" 2>&1
else
    echo -e "${YELLOW}⚠ La tabla 'sessions' NO existe${NC}"
    echo "Ejecuta: php artisan session:table && php artisan migrate"
fi
echo ""

# 4. Verificar configuración de bind-address
echo "4. Verificando configuración de bind-address..."
MYSQL_CONFIG=$(mysql -u laravel_user -p'Tiziano2025!Laravel' -e "SHOW VARIABLES LIKE 'bind_address';" 2>/dev/null | grep bind_address || echo "No se pudo obtener")
echo "bind_address: $MYSQL_CONFIG"
if echo "$MYSQL_CONFIG" | grep -q "127.0.0.1\|localhost"; then
    echo -e "${GREEN}✓ MySQL está configurado para aceptar conexiones locales${NC}"
else
    echo -e "${YELLOW}⚠ Verificar configuración de bind-address en /etc/mysql/my.cnf${NC}"
fi
echo ""

# 5. Verificar logs de MySQL
echo "5. Verificando logs recientes de MySQL..."
if [ -f /var/log/mysql/error.log ]; then
    echo "Últimas 10 líneas del log de errores:"
    tail -n 10 /var/log/mysql/error.log
elif [ -f /var/log/mysqld.log ]; then
    echo "Últimas 10 líneas del log:"
    tail -n 10 /var/log/mysqld.log
else
    echo -e "${YELLOW}⚠ No se encontró archivo de log estándar${NC}"
fi
echo ""

# 6. Verificar permisos del usuario
echo "6. Verificando permisos del usuario laravel_user..."
mysql -u laravel_user -p'Tiziano2025!Laravel' laravel_admin -e "SHOW GRANTS;" 2>&1
echo ""

# 7. Verificar variables de entorno de Laravel
echo "7. Verificando configuración de Laravel..."
if [ -f .env ]; then
    echo "Variables de base de datos en .env:"
    grep -E "^DB_" .env | sed 's/\(PASSWORD=\).*/\1***OCULTO***/'
else
    echo -e "${YELLOW}⚠ Archivo .env no encontrado en el directorio actual${NC}"
    echo "Busca el archivo .env en el directorio raíz de Laravel"
fi
echo ""

# 8. Verificar puerto MySQL
echo "8. Verificando puerto MySQL..."
MYSQL_PORT=$(mysql -u laravel_user -p'Tiziano2025!Laravel' -e "SHOW VARIABLES LIKE 'port';" 2>/dev/null | grep port | awk '{print $2}' || echo "3306")
echo "Puerto MySQL: $MYSQL_PORT"
netstat -tlnp | grep ":$MYSQL_PORT" || ss -tlnp | grep ":$MYSQL_PORT"
echo ""

echo "=========================================="
echo "Diagnóstico completado"
echo "=========================================="
echo ""
echo "Si todos los checks pasaron pero el problema persiste:"
echo "1. Verifica que el firewall permita conexiones en el puerto MySQL"
echo "2. Verifica los logs de Laravel: tail -f storage/logs/laravel.log"
echo "3. Limpia la caché de configuración: php artisan config:clear"
echo "4. Verifica que el servidor web tenga permisos para conectarse a MySQL"

