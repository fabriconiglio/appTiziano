#!/bin/bash

# Script de gestión para alertas de stock en PRODUCCIÓN
# Servidor: 165.227.202.245
# Proyecto: /var/www/laravel-admin

SERVER="root@165.227.202.245"
PROJECT_PATH="/var/www/laravel-admin"

case "$1" in
    "status")
        echo "=== Estado del Sistema de Alertas de Stock (PRODUCCIÓN) ==="
        echo "Servidor: $SERVER"
        echo "Proyecto: $PROJECT_PATH"
        echo ""
        ssh $SERVER "cd $PROJECT_PATH && php artisan tinker --execute=\"echo 'Total de alertas: ' . \App\Models\StockAlert::count(); echo PHP_EOL; echo 'Alertas no leídas: ' . \App\Models\StockAlert::where('is_read', false)->count(); echo PHP_EOL; echo 'Alertas de peluquería: ' . \App\Models\StockAlert::where('inventory_type', 'peluqueria')->count(); echo PHP_EOL; echo 'Alertas de distribuidora: ' . \App\Models\StockAlert::where('inventory_type', 'distribuidora')->count();\""
        echo ""
        echo "Cron configurado:"
        ssh $SERVER "crontab -l | grep -E '(stock|queue)'"
        ;;
    "check")
        echo "Ejecutando verificación manual de stock en producción..."
        ssh $SERVER "cd $PROJECT_PATH && php artisan stock:check-cron"
        echo "Procesando cola de trabajos..."
        ssh $SERVER "cd $PROJECT_PATH && php artisan queue:work --once"
        ;;
    "clear")
        echo "Limpiando alertas leídas en producción..."
        ssh $SERVER "cd $PROJECT_PATH && php artisan tinker --execute=\"\App\Models\StockAlert::where('is_read', true)->delete(); echo 'Alertas leídas eliminadas';\""
        ;;
    "reset")
        echo "Eliminando todas las alertas en producción..."
        ssh $SERVER "cd $PROJECT_PATH && php artisan tinker --execute=\"\App\Models\StockAlert::truncate(); echo 'Todas las alertas eliminadas';\""
        ;;
    "logs")
        echo "Mostrando logs de alertas de stock en producción..."
        ssh $SERVER "tail -f $PROJECT_PATH/storage/logs/laravel.log | grep -i stock"
        ;;
    "test")
        echo "Probando sistema completo en producción..."
        echo "1. Verificando productos con stock bajo..."
        ssh $SERVER "cd $PROJECT_PATH && php artisan tinker --execute=\"echo 'Productos con stock <= 5: ' . \App\Models\Product::where('current_stock', '<=', 5)->count(); echo PHP_EOL; echo 'Inventario proveedores con stock <= 5: ' . \App\Models\SupplierInventory::where('stock_quantity', '<=', 5)->count();\""
        echo ""
        echo "2. Ejecutando verificación de stock..."
        ssh $SERVER "cd $PROJECT_PATH && php artisan stock:check-cron"
        echo ""
        echo "3. Procesando cola de trabajos..."
        ssh $SERVER "cd $PROJECT_PATH && php artisan queue:work --once"
        echo ""
        echo "4. Verificando alertas creadas..."
        ssh $SERVER "cd $PROJECT_PATH && php artisan tinker --execute=\"echo 'Total de alertas: ' . \App\Models\StockAlert::count(); echo PHP_EOL; echo 'Alertas no leídas: ' . \App\Models\StockAlert::where('is_read', false)->count();\""
        ;;
    "deploy")
        echo "Desplegando configuración de alertas de stock en producción..."
        echo "1. Copiando archivos de configuración..."
        scp setup_stock_alerts_cron.sh $SERVER:/tmp/
        scp stock_alerts_management.sh $SERVER:/tmp/
        echo ""
        echo "2. Configurando permisos..."
        ssh $SERVER "chmod +x /tmp/setup_stock_alerts_cron.sh /tmp/stock_alerts_management.sh"
        echo ""
        echo "3. Configurando cron (ya está configurado)..."
        echo "4. Verificando configuración..."
        ssh $SERVER "crontab -l | grep -E '(stock|queue)'"
        echo ""
        echo "✅ Configuración completada en producción"
        ;;
    "help"|*)
        echo "Gestión de Alertas de Stock - PRODUCCIÓN"
        echo "Servidor: $SERVER"
        echo "Proyecto: $PROJECT_PATH"
        echo ""
        echo "Uso: $0 [comando]"
        echo ""
        echo "Comandos disponibles:"
        echo "  status     - Mostrar estado del sistema en producción"
        echo "  check      - Ejecutar verificación manual en producción"
        echo "  clear      - Limpiar alertas leídas en producción"
        echo "  reset      - Eliminar todas las alertas en producción"
        echo "  logs       - Ver logs de alertas en producción"
        echo "  test       - Probar sistema completo en producción"
        echo "  deploy     - Desplegar configuración en producción"
        echo "  help       - Mostrar esta ayuda"
        ;;
esac
