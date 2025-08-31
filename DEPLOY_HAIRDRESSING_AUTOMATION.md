# 🚀 Automatización CI/CD - Módulo de Ventas por Día - Peluquería

## 📋 Descripción

Este documento explica cómo el módulo de **"Ventas por Día - Peluquería"** se configura automáticamente durante el proceso de deploy en GitHub Actions, asegurando que el cron job esté siempre configurado correctamente.

## 🔄 Flujo de Automatización

### **1. Trigger del Deploy**
- **Evento**: Push a la rama `main` o `master`
- **Plataforma**: GitHub Actions
- **Workflow**: `.github/workflows/deploy.yml`

### **2. Proceso de Deploy**
```yaml
# Pasos del workflow
1. Checkout del código
2. Setup de PHP y dependencias
3. Deploy al servidor via SSH
4. Configuración automática de cron jobs
5. Verificación del deploy
```

### **3. Configuración Automática de Cron Jobs**
```bash
# Script ejecutado automáticamente
./deploy_setup_hairdressing_cron.sh
```

## ⚙️ Scripts de Automatización

### **Script Principal de Deploy**
- **Archivo**: `deploy_setup_hairdressing_cron.sh`
- **Propósito**: Configurar cron job durante el deploy
- **Ejecución**: Automática en GitHub Actions

### **Script Local de Desarrollo**
- **Archivo**: `setup_hairdressing_daily_sales_cron.sh`
- **Propósito**: Configurar cron job en desarrollo local
- **Ejecución**: Manual cuando sea necesario

## 🎯 Funcionalidades del Script de Deploy

### **Verificaciones Automáticas**
1. ✅ **Proyecto Laravel**: Verifica que `artisan` esté presente
2. ✅ **Comando Disponible**: Verifica que `hairdressing-sales:reset-daily` exista
3. ✅ **Funcionamiento**: Prueba el comando antes de configurar
4. ✅ **Ruta del Proyecto**: Obtiene la ruta absoluta automáticamente

### **Configuración del Cron Job**
```bash
# Cron job configurado automáticamente
0 0 * * * cd /ruta/del/proyecto && php artisan hairdressing-sales:reset-daily >> storage/logs/hairdressing-daily-sales-cron.log 2>&1
```

**Explicación**:
- **`0 0 * * *`**: Ejecutar diariamente a las 00:00
- **`cd /ruta/del/proyecto`**: Navegar al directorio del proyecto
- **`php artisan hairdressing-sales:reset-daily`**: Ejecutar el comando
- **`>> storage/logs/hairdressing-daily-sales-cron.log 2>&1`**: Guardar logs

### **Gestión Inteligente**
- 🔄 **Actualización**: Si el cron job ya existe, lo actualiza
- 📝 **Logging**: Registra todas las operaciones
- ❌ **Manejo de Errores**: Falla graciosamente si hay problemas

## 📊 Logs y Monitoreo

### **Logs del Deploy**
- **Ubicación**: Salida del workflow de GitHub Actions
- **Contenido**: Estado de la configuración del cron job
- **Formato**: Emojis y mensajes descriptivos

### **Logs del Cron Job**
- **Ubicación**: `storage/logs/hairdressing-daily-sales-cron.log`
- **Contenido**: Ejecuciones diarias del reset
- **Formato**: Timestamp + Resultado de la ejecución

### **Ejemplo de Logs**
```bash
# Log del deploy
🎯 Configurando cron job para Ventas por Día - Peluquería (Deploy)
✅ Comando encontrado correctamente
✅ Comando ejecutado exitosamente
✅ Cron job configurado exitosamente

# Log del cron job
2025-08-31 00:00:01 - Estadísticas de ventas diarias de peluquería reseteadas automáticamente
```

## 🔧 Configuración del Workflow

### **Archivo de Workflow**
```yaml
# .github/workflows/deploy.yml
- name: Deploy to server
  uses: appleboy/ssh-action@v1.0.3
  with:
    host: ${{ secrets.HOST }}
    username: ${{ secrets.USERNAME }}
    key: ${{ secrets.KEY }}
    script: |
      # ... código de deploy ...
      
      # Configurar cron job para Ventas por Día - Peluquería
      echo "⏰ Configurando cron job para Ventas por Día - Peluquería..."
      chmod +x deploy_setup_hairdressing_cron.sh
      ./deploy_setup_hairdressing_cron.sh
```

### **Secrets Requeridos**
- **`HOST`**: IP o dominio del servidor
- **`USERNAME`**: Usuario SSH del servidor
- **`KEY`**: Clave privada SSH para autenticación

## 🚨 Manejo de Errores

### **Errores del Script**
1. **Proyecto no es Laravel**: Falla si no encuentra `artisan`
2. **Comando no disponible**: Falla si el comando no existe
3. **Error de ejecución**: Falla si el comando falla
4. **Error de cron**: Falla si no se puede configurar el cron

### **Fallbacks y Recuperación**
- **Deploy falla**: El cron job no se configura
- **Cron falla**: Se mantiene la configuración anterior
- **Logs**: Siempre se registran los errores para debugging

## 📈 Beneficios de la Automatización

### **Para Desarrolladores**
- 🚀 **Deploy sin intervención manual**
- 🔄 **Cron jobs siempre actualizados**
- 📝 **Logs automáticos para debugging**
- ⚡ **Configuración consistente entre entornos**

### **Para Operaciones**
- 🎯 **Configuración automática y confiable**
- 📊 **Monitoreo centralizado**
- 🔧 **Mantenimiento reducido**
- 🚨 **Alertas automáticas en caso de fallo**

## 🧪 Testing y Verificación

### **Verificación Local**
```bash
# Probar el script localmente
./deploy_setup_hairdressing_cron.sh

# Verificar que el cron job esté configurado
crontab -l | grep hairdressing-sales
```

### **Verificación en Producción**
```bash
# Verificar logs del deploy
tail -f storage/logs/hairdressing-daily-sales-cron.log

# Verificar estado del cron
crontab -l | grep hairdressing-sales
```

## 🔄 Actualizaciones y Mantenimiento

### **Modificar el Script**
1. **Editar** `deploy_setup_hairdressing_cron.sh`
2. **Commit y push** a la rama principal
3. **Deploy automático** ejecutará el script actualizado

### **Modificar el Workflow**
1. **Editar** `.github/workflows/deploy.yml`
2. **Commit y push** a la rama principal
3. **Workflow actualizado** se ejecutará en el próximo deploy

## 📚 Referencias

### **Archivos Relacionados**
- `deploy_setup_hairdressing_cron.sh` - Script de deploy
- `setup_hairdressing_daily_sales_cron.sh` - Script local
- `.github/workflows/deploy.yml` - Workflow de GitHub Actions
- `app/Console/Commands/ResetHairdressingDailySales.php` - Comando Artisan

### **Comandos Útiles**
```bash
# Verificar estado del cron
crontab -l

# Ejecutar reset manual
php artisan hairdressing-sales:reset-daily

# Ver logs del cron
tail -f storage/logs/hairdressing-daily-sales-cron.log
```

---

**Sistema Tiziano** - Automatización CI/CD - Módulo de Ventas por Día - Peluquería  
*Última actualización: {{ date('d/m/Y') }}* 