# Módulo de Ventas por Día - Configuración y Uso

## Descripción
El módulo "Ventas por Día" es un dashboard que muestra las estadísticas de ventas diarias del sistema, incluyendo presupuestos, fichas técnicas y cuentas corrientes. Se resetea automáticamente cada día a las 00:00.

## Características Principales

### 📊 Dashboard Principal
- **Total del Día**: Suma de todas las ventas del día actual
- **Presupuestos**: Ventas de presupuestos convertidos
- **Fichas Técnicas**: Ventas de fichas técnicas
- **Cuentas Corrientes**: Ventas de cuentas corrientes (clientes y distribuidores)

### 📈 Comparaciones
- Comparación con el día anterior
- Estadísticas del mes actual
- Proyección mensual basada en el promedio diario

### 🕐 Análisis por Hora
- Gráfico de ventas por hora del día
- Tabla detallada con desglose por tipo de venta
- Actualización automática cada 5 minutos

### 📄 Exportación
- Exportación a PDF del reporte diario
- Formato profesional para presentaciones

## Configuración del Cron Job

Para que el módulo se resetee automáticamente cada día, es necesario configurar un cron job en el servidor.

### 1. Configuración en el Servidor

Edita el crontab del usuario:
```bash
crontab -e
```

### 2. Agregar la Entrada del Cron

Agrega la siguiente línea para ejecutar el comando cada día a las 00:00:
```bash
0 0 * * * cd /ruta/a/tu/proyecto && php artisan sales:reset-daily >> /dev/null 2>&1
```

**Ejemplo con ruta completa:**
```bash
0 0 * * * cd /home/fabrizzio/Escritorio/appTiziano && php artisan sales:reset-daily >> /dev/null 2>&1
```

### 3. Verificar la Configuración

Para verificar que el cron job esté configurado correctamente:
```bash
crontab -l
```

### 4. Probar el Comando Manualmente

Puedes probar el comando manualmente para verificar que funcione:
```bash
php artisan sales:reset-daily
```

## Estructura de Archivos

```
app/
├── Http/Controllers/
│   └── DailySalesController.php          # Controlador principal
├── Console/Commands/
│   └── ResetDailySales.php               # Comando de reseteo
resources/
├── views/
│   └── daily_sales/
│       ├── index.blade.php               # Vista principal
│       └── pdf.blade.php                 # Vista para PDF
routes/
├── web.php                               # Rutas web del módulo
└── console.php                           # Comando de consola
```

## Rutas Disponibles

- `GET /daily-sales` - Dashboard principal
- `GET /daily-sales/chart-data` - Datos para gráficos (API)
- `GET /daily-sales/export-pdf` - Exportar a PDF

## Acceso al Módulo

El módulo está disponible en el menú principal bajo:
**Distribuidora → Ventas por Día**

## Funcionalidades Técnicas

### Auto-Reseteo
- Se ejecuta automáticamente cada día a las 00:00
- Limpia logs y estadísticas del día anterior
- Prepara el sistema para el nuevo día

### Actualización en Tiempo Real
- Los datos se actualizan automáticamente cada 5 minutos
- Botón manual de actualización disponible
- Gráficos interactivos con Chart.js

### Cálculos Automáticos
- Suma automática de ventas por tipo
- Comparación porcentual con días anteriores
- Proyecciones basadas en promedios históricos

## Personalización

### Modificar Tipos de Venta
Para agregar o modificar los tipos de venta que se muestran, edita el método `getDailySales()` en `DailySalesController.php`.

### Cambiar Frecuencia de Actualización
Para modificar la frecuencia de actualización automática, edita el JavaScript en la vista `index.blade.php`:

```javascript
// Cambiar de 5 minutos a otro valor
setInterval(function() {
    location.reload();
}, 10 * 60 * 1000); // 10 minutos
```

### Modificar Horario de Reseteo
Para cambiar el horario del reseteo automático, edita el archivo `routes/console.php`:

```php
})->dailyAt('06:00'); // Cambiar a las 6:00 AM
```

## Solución de Problemas

### El módulo no se resetea automáticamente
1. Verificar que el cron job esté configurado correctamente
2. Verificar permisos del usuario del cron
3. Revisar logs del sistema: `tail -f /var/log/syslog`

### Los datos no se actualizan
1. Verificar que el comando `sales:reset-daily` funcione manualmente
2. Revisar logs de Laravel: `storage/logs/laravel.log`
3. Verificar que no haya errores en la consola del navegador

### Problemas con el PDF
1. Verificar que la librería DomPDF esté instalada
2. Verificar permisos de escritura en `storage/`
3. Revisar logs de Laravel para errores específicos

## Logs y Auditoría

El sistema registra automáticamente:
- Fecha y hora de cada reseteo
- Errores durante el proceso
- Usuario del sistema que ejecutó el reseteo
- Estadísticas del día anterior

Los logs se encuentran en:
- `storage/logs/laravel.log` - Logs generales de Laravel
- Logs del sistema (syslog) - Para el cron job

## Soporte

Para reportar problemas o solicitar mejoras:
1. Revisar los logs del sistema
2. Verificar la configuración del cron job
3. Probar comandos manualmente
4. Contactar al equipo de desarrollo

---

**Nota**: Este módulo está diseñado para funcionar de manera completamente automática. Una vez configurado el cron job, no requiere intervención manual para su funcionamiento diario. 