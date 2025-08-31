#!/bin/bash

# Script para configurar el cron job de reset diario de ventas de peluquería
# Este script se ejecuta durante el deploy en GitHub Actions
# Tiziano - Sistema de Gestión

echo "🎯 Configurando cron job para Ventas por Día - Peluquería (Deploy)"
echo "=================================================================="

# Verificar que estamos en un proyecto Laravel
if [ ! -f "artisan" ]; then
    echo "❌ Error: No se encontró el archivo artisan. Asegúrate de estar en el directorio raíz del proyecto Laravel."
    exit 1
fi

# Verificar que el comando existe
echo "🔍 Verificando comando hairdressing-sales:reset-daily..."
if php artisan list | grep -q "hairdressing-sales:reset-daily"; then
    echo "✅ Comando encontrado correctamente"
else
    echo "❌ Error: El comando hairdressing-sales:reset-daily no está disponible"
    echo "💡 Ejecuta: php artisan hairdressing-sales:reset-daily"
    exit 1
fi

# Probar el comando
echo "🧪 Probando comando..."
if php artisan hairdressing-sales:reset-daily > /dev/null 2>&1; then
    echo "✅ Comando ejecutado exitosamente"
else
    echo "❌ Error al ejecutar el comando"
    exit 1
fi

# Obtener la ruta absoluta del proyecto
PROJECT_PATH=$(pwd)
echo "📁 Ruta del proyecto: $PROJECT_PATH"

# Crear el cron job
CRON_JOB="0 0 * * * cd $PROJECT_PATH && php artisan hairdressing-sales:reset-daily >> storage/logs/hairdressing-daily-sales-cron.log 2>&1"

echo "⏰ Configurando cron job para ejecutarse diariamente a las 00:00..."
echo "📝 Cron job: $CRON_JOB"

# Verificar si ya existe el cron job
if crontab -l 2>/dev/null | grep -q "hairdressing-sales:reset-daily"; then
    echo "🔄 Cron job ya existe, actualizando..."
    # Remover el cron job existente
    crontab -l 2>/dev/null | grep -v "hairdressing-sales:reset-daily" | crontab -
fi

# Agregar el nuevo cron job
(crontab -l 2>/dev/null; echo "$CRON_JOB") | crontab -

if [ $? -eq 0 ]; then
    echo "✅ Cron job configurado exitosamente"
    echo ""
    echo "📋 Cron jobs actuales:"
    crontab -l 2>/dev/null | grep -E "(daily|sales|reset|hairdressing)"
    echo ""
    echo "📊 El sistema reseteará automáticamente las estadísticas de ventas de peluquería cada día a las 00:00"
    echo "📝 Los logs se guardarán en: storage/logs/hairdressing-daily-sales-cron.log"
else
    echo "❌ Error al configurar el cron job"
    exit 1
fi

echo ""
echo "🎉 Configuración completada exitosamente durante el deploy!"
echo "💡 Para verificar manualmente, ejecuta: php artisan hairdressing-sales:reset-daily" 