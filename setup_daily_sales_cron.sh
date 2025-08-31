#!/bin/bash

# Script de configuración para el módulo de Ventas por Día
# Este script configura automáticamente el cron job necesario

echo "🚀 Configurando módulo de Ventas por Día..."
echo "=============================================="

# Obtener la ruta actual del proyecto
PROJECT_PATH=$(pwd)
echo "📁 Ruta del proyecto: $PROJECT_PATH"

# Verificar que estamos en un proyecto Laravel
if [ ! -f "artisan" ]; then
    echo "❌ Error: No se encontró el archivo 'artisan'. Asegúrate de estar en el directorio raíz del proyecto Laravel."
    exit 1
fi

echo "✅ Proyecto Laravel detectado correctamente"

# Verificar que el comando existe
echo "🔍 Verificando que el comando de ventas diarias esté disponible..."
if php artisan list | grep -q "sales:reset-daily"; then
    echo "✅ Comando 'sales:reset-daily' encontrado"
else
    echo "❌ Error: El comando 'sales:reset-daily' no está disponible."
    echo "   Asegúrate de que el archivo routes/console.php contenga el comando."
    exit 1
fi

# Probar el comando manualmente
echo "🧪 Probando el comando manualmente..."
if php artisan sales:reset-daily; then
    echo "✅ Comando ejecutado correctamente"
else
    echo "❌ Error al ejecutar el comando. Revisa los logs de Laravel."
    exit 1
fi

# Configurar el cron job
echo "⏰ Configurando cron job..."
CRON_ENTRY="0 0 * * * cd $PROJECT_PATH && php artisan sales:reset-daily >> /dev/null 2>&1"

# Verificar si ya existe la entrada
if crontab -l 2>/dev/null | grep -q "sales:reset-daily"; then
    echo "⚠️  El cron job ya está configurado. Actualizando..."
    # Remover entrada existente
    crontab -l 2>/dev/null | grep -v "sales:reset-daily" | crontab -
fi

# Agregar nueva entrada
(crontab -l 2>/dev/null; echo "$CRON_ENTRY") | crontab -

if [ $? -eq 0 ]; then
    echo "✅ Cron job configurado correctamente"
else
    echo "❌ Error al configurar el cron job"
    exit 1
fi

# Verificar la configuración
echo "🔍 Verificando configuración del cron job..."
if crontab -l | grep -q "sales:reset-daily"; then
    echo "✅ Cron job verificado:"
    crontab -l | grep "sales:reset-daily"
else
    echo "❌ Error: El cron job no se configuró correctamente"
    exit 1
fi

echo ""
echo "🎉 Configuración completada exitosamente!"
echo "=============================================="
echo "📊 El módulo de Ventas por Día se reseteará automáticamente cada día a las 00:00"
echo "🌐 Accede al módulo desde: Distribuidora → Ventas por Día"
echo ""
echo "📋 Próximos pasos:"
echo "1. Verifica que el módulo esté funcionando visitando la ruta /daily-sales"
echo "2. Revisa los logs en storage/logs/laravel.log para verificar el reseteo automático"
echo "3. Si tienes problemas, ejecuta manualmente: php artisan sales:reset-daily"
echo ""
echo "📚 Para más información, consulta el archivo DAILY_SALES_SETUP.md"
echo ""
echo "🔄 El sistema se reseteará automáticamente mañana a las 00:00" 