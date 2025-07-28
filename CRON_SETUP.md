# Configuración de Cron para Alertas de Stock

## Instrucciones para configurar el cron en producción

### 1. Acceder al servidor de producción
```bash
ssh root@tu-servidor
cd /var/www/laravel-admin
```

### 2. Verificar que el comando funciona
```bash
php artisan stock:check-cron --help
```

### 3. Probar el comando manualmente
```bash
php artisan stock:check-cron
```

### 4. Configurar el cron del sistema

#### Opción A: Usando crontab
```bash
# Editar el crontab
crontab -e

# Agregar la siguiente línea para ejecutar cada hora
0 * * * * cd /var/www/laravel-admin && php artisan stock:check-cron >> /dev/null 2>&1

# O para ejecutar cada 30 minutos
*/30 * * * * cd /var/www/laravel-admin && php artisan stock:check-cron >> /dev/null 2>&1

# O para ejecutar cada día a las 8:00 AM
0 8 * * * cd /var/www/laravel-admin && php artisan stock:check-cron >> /dev/null 2>&1
```

#### Opción B: Usando el cron de Laravel (Recomendado)
```bash
# Editar el archivo de cron de Laravel
nano app/Console/Kernel.php
```

Agregar en el método `schedule()`:
```php
protected function schedule(Schedule $schedule): void
{
    // Verificar stock bajo cada hora
    $schedule->command('stock:check-cron')->hourly();
    
    // O cada 30 minutos
    // $schedule->command('stock:check-cron')->everyThirtyMinutes();
    
    // O diariamente a las 8:00 AM
    // $schedule->command('stock:check-cron')->dailyAt('08:00');
}
```

Luego configurar el cron del sistema para ejecutar el scheduler de Laravel:
```bash
# Editar crontab
crontab -e

# Agregar esta línea
* * * * * cd /var/www/laravel-admin && php artisan schedule:run >> /dev/null 2>&1
```

### 5. Verificar que el cron está funcionando

#### Verificar logs de Laravel:
```bash
tail -f storage/logs/laravel.log
```

#### Verificar que el cron está activo:
```bash
crontab -l
```

#### Verificar que el scheduler está funcionando:
```bash
php artisan schedule:list
```

### 6. Configuraciones adicionales recomendadas

#### Configurar umbral personalizado:
```bash
# En el crontab o scheduler
php artisan stock:check-cron --threshold=10
```

#### Configurar logs específicos:
```bash
# Crear un log específico para las alertas
php artisan stock:check-cron >> storage/logs/stock-alerts.log 2>&1
```

### 7. Monitoreo y mantenimiento

#### Verificar alertas creadas:
```bash
php artisan tinker --execute="echo 'Alertas totales: ' . App\Models\StockAlert::count() . PHP_EOL;"
```

#### Limpiar alertas antiguas (opcional):
```bash
# Crear un comando para limpiar alertas leídas de más de 30 días
php artisan make:command CleanOldStockAlerts
```

### 8. Troubleshooting

#### Si el cron no funciona:
1. Verificar permisos del directorio
2. Verificar que PHP está en el PATH
3. Verificar logs del sistema: `tail -f /var/log/cron`
4. Verificar logs de Laravel: `tail -f storage/logs/laravel.log`

#### Si las alertas no se crean:
1. Verificar que hay productos con stock bajo
2. Verificar la configuración de email
3. Verificar que las colas están funcionando

### 9. Comandos útiles para monitoreo

```bash
# Ver estado de las colas
php artisan queue:work --once

# Ver alertas no leídas
php artisan tinker --execute="echo 'Alertas no leídas: ' . App\Models\StockAlert::where('is_read', false)->count() . PHP_EOL;"

# Ver alertas por tipo
php artisan tinker --execute="echo 'Peluquería: ' . App\Models\StockAlert::where('inventory_type', 'peluqueria')->count() . PHP_EOL; echo 'Distribuidora: ' . App\Models\StockAlert::where('inventory_type', 'distribuidora')->count() . PHP_EOL;"
``` 