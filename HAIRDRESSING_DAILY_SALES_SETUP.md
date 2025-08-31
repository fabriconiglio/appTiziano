# 🎯 Módulo de Ventas por Día - Peluquería

## 📋 Descripción

El módulo **"Ventas por Día - Peluquería"** es un dashboard completo que muestra las estadísticas diarias de ventas del negocio de peluquería, incluyendo servicios, productos vendidos y cuentas corrientes de clientes.

## ✨ Características Principales

### 📊 **Dashboard en Tiempo Real**
- **Total del Día**: Suma de todas las ventas del día
- **Cuentas Corrientes**: Ventas de deudas de clientes
- **Servicios**: Estimación basada en fichas técnicas ($50 por servicio)
- **Productos**: Ventas reales de productos del inventario

### 🔍 **Análisis Avanzado**
- **Comparación con ayer** con porcentajes y diferencias
- **Estadísticas del mes** con proyecciones mensuales
- **Servicios más populares** del día
- **Productos más vendidos** del día con cantidades y montos
- **Gráfico de ventas por hora** interactivo con Chart.js
- **Tabla detallada** por hora con desglose completo

### 🎛️ **Funcionalidades Adicionales**
- **Selector de fecha** para consultar días anteriores
- **Auto-reseteo diario** a las 00:00
- **Actualización automática** cada 5 minutos (solo para fecha actual)
- **Exportación a PDF** del reporte completo
- **Filtros históricos** para análisis de fechas pasadas

## 🏗️ Estructura del Módulo

### **Archivos del Controlador**
```
app/Http/Controllers/HairdressingDailySalesController.php
```

### **Vistas del Módulo**
```
resources/views/hairdressing_daily_sales/
├── index.blade.php          # Dashboard principal
└── pdf.blade.php            # Vista para exportación PDF
```

### **Rutas del Módulo**
```
GET /hairdressing-daily-sales                    # Dashboard principal
GET /hairdressing-daily-sales/chart-data         # Datos para gráficos
GET /hairdressing-daily-sales/export-pdf         # Exportar a PDF
```

### **Comando Artisan**
```
php artisan hairdressing-sales:reset-daily       # Reset manual
```

## 🚀 Instalación y Configuración

### **1. Verificar Dependencias**
```bash
# Asegurarse de que las migraciones estén ejecutadas
php artisan migrate

# Verificar que el comando esté disponible
php artisan list | grep hairdressing-sales
```

### **2. Configurar Cron Job Automático**
```bash
# Hacer ejecutable el script
chmod +x setup_hairdressing_daily_sales_cron.sh

# Ejecutar el script de configuración
./setup_hairdressing_daily_sales_cron.sh
```

### **3. Verificar Configuración**
```bash
# Ver cron jobs activos
crontab -l

# Probar el comando manualmente
php artisan hairdressing-sales:reset-daily
```

## 📈 Modelo de Datos

### **Fuentes de Datos**
1. **ClientCurrentAccount**: Cuentas corrientes de clientes (deudas)
2. **TechnicalRecord**: Fichas técnicas de servicios realizados
3. **StockMovement**: Movimientos de inventario (salidas = ventas)
4. **Product**: Información de productos y precios

### **Cálculos de Ventas**
- **Servicios**: `count(technical_records) × $50`
- **Productos**: `sum(quantity × product.price)` de movimientos tipo 'salida'
- **Cuentas Corrientes**: `sum(amount)` de movimientos tipo 'debt'
- **Total**: Suma de todos los tipos de venta

## 🎨 Personalización

### **Colores del Tema**
- **Principal**: Azul (`#007bff`) - Total del día
- **Éxito**: Verde (`#28a745`) - Cuentas corrientes
- **Info**: Azul claro (`#17a2b8`) - Servicios
- **Advertencia**: Amarillo (`#ffc107`) - Productos

### **Estimaciones de Precios**
```php
// En el controlador se pueden ajustar estos valores:
$servicePrice = 50;        // Precio por servicio
$additionalServicePrice = 25; // Precio por servicio adicional
```

## 🔧 Mantenimiento

### **Logs del Sistema**
- **Ubicación**: `storage/logs/hairdressing-daily-sales-cron.log`
- **Contenido**: Registros de reseteo automático diario
- **Formato**: Timestamp + Acción + Resultado

### **Monitoreo del Cron Job**
```bash
# Ver logs del cron job
tail -f storage/logs/hairdressing-daily-sales-cron.log

# Verificar estado del cron
crontab -l | grep hairdressing-sales
```

### **Reseteo Manual**
```bash
# Ejecutar reset manual
php artisan hairdressing-sales:reset-daily

# Ver logs de la ejecución
tail -n 20 storage/logs/laravel.log
```

## 🚨 Solución de Problemas

### **Error: "Column not found: unit_price"**
- **Causa**: La tabla `stock_movements` no tiene columna `unit_price`
- **Solución**: El sistema usa `products.price` en su lugar

### **Error: "Route not found"**
- **Causa**: Las rutas no están registradas
- **Solución**: Verificar `routes/web.php` y ejecutar `php artisan route:clear`

### **Error: "Class not found"**
- **Causa**: El controlador no existe o tiene errores de sintaxis
- **Solución**: Verificar que `HairdressingDailySalesController.php` esté en la ubicación correcta

### **Cron Job No Ejecuta**
- **Causa**: Configuración incorrecta del cron
- **Solución**: Ejecutar `./setup_hairdressing_daily_sales_cron.sh` nuevamente

## 📱 Acceso al Módulo

### **Desde la Página Principal**
1. Ir a la página de inicio (`/`)
2. En la sección **"Módulo Peluquería"**
3. Hacer clic en **"Ventas por Día"** (botón azul)

### **Acceso Directo**
```
http://tu-dominio.com/hairdressing-daily-sales
```

## 🔄 Actualizaciones y Mejoras

### **Versión Actual**: 1.0
- Dashboard básico funcional
- Exportación a PDF
- Cron job automático
- Filtros de fecha

### **Próximas Mejoras**
- Gráficos comparativos entre días
- Exportación a Excel
- Notificaciones de alertas
- Integración con calendario de citas

## 📞 Soporte

Para reportar problemas o solicitar mejoras:
1. Verificar logs del sistema
2. Revisar configuración del cron job
3. Probar comandos manualmente
4. Contactar al equipo de desarrollo

---

**Sistema Tiziano** - Módulo de Ventas por Día - Peluquería  
*Versión 1.0 - Última actualización: {{ date('d/m/Y') }}* 