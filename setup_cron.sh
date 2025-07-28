#!/bin/bash

# Script para configurar el cron de alertas de stock en producción
# Uso: ./setup_cron.sh

echo "=== Configuración de Cron para Alertas de Stock ==="
echo ""

# Verificar que estamos en el directorio correcto
if [ ! -f "artisan" ]; then
    echo "Error: No se encontró el archivo artisan. Asegúrate de estar en el directorio raíz de Laravel."
    exit 1
fi

echo "1. Verificando que el comando funciona..."
php artisan stock:check-cron --help

if [ $? -ne 0 ]; then
    echo "Error: El comando no funciona correctamente."
    exit 1
fi

echo ""
echo "2. Probando el comando manualmente..."
php artisan stock:check-cron

echo ""
echo "3. Configurando el cron del sistema..."

# Verificar si ya existe el cron
if crontab -l 2>/dev/null | grep -q "stock:check-cron"; then
    echo "El cron ya está configurado. ¿Deseas sobrescribirlo? (y/n)"
    read -r response
    if [[ "$response" =~ ^([yY][eE][sS]|[yY])$ ]]; then
        echo "Eliminando configuración anterior..."
        crontab -l 2>/dev/null | grep -v "stock:check-cron" | crontab -
    else
        echo "Configuración cancelada."
        exit 0
    fi
fi

# Obtener el directorio actual
CURRENT_DIR=$(pwd)

echo "4. Agregando entrada al crontab..."
echo "Directorio del proyecto: $CURRENT_DIR"

# Crear entrada temporal del cron
TEMP_CRON=$(mktemp)
crontab -l 2>/dev/null > "$TEMP_CRON"

# Agregar la nueva entrada (cada hora)
echo "# Verificación automática de alertas de stock - Laravel Tiziano" >> "$TEMP_CRON"
echo "0 * * * * cd $CURRENT_DIR && php artisan stock:check-cron >> /dev/null 2>&1" >> "$TEMP_CRON"

# Instalar el nuevo crontab
crontab "$TEMP_CRON"
rm "$TEMP_CRON"

echo ""
echo "5. Verificando la configuración..."
crontab -l

echo ""
echo "6. Configuración completada exitosamente!"
echo ""
echo "El cron se ejecutará cada hora (a la hora en punto)."
echo ""
echo "Para verificar que funciona:"
echo "  - Revisar logs: tail -f storage/logs/laravel.log"
echo "  - Ver alertas: php artisan tinker --execute=\"echo 'Alertas: ' . App\Models\StockAlert::count() . PHP_EOL;\""
echo ""
echo "Para cambiar la frecuencia, edita el crontab con: crontab -e"
echo ""
echo "Frecuencias comunes:"
echo "  - Cada 30 minutos: */30 * * * *"
echo "  - Cada 2 horas: 0 */2 * * *"
echo "  - Diario a las 8 AM: 0 8 * * *"
echo ""
echo "¡Configuración completada!" 