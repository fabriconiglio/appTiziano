# 🚀 Automatización del Cron Job en Deploy

## 📋 **Resumen**
Este documento explica cómo configurar el cron job del módulo "Ventas por Día" para que se ejecute automáticamente después de cada deploy, sin intervención manual.

## 🔧 **Opciones de Automatización**

### **Opción 1: Script de Deploy Automático (Recomendado)**

#### **Para Deploy Manual:**
```bash
# Después de hacer git pull en el servidor
git pull origin main
./deploy_setup_cron.sh
```

#### **Para Deploy Automático (Git Hooks):**
```bash
# Crear post-merge hook
cat > .git/hooks/post-merge << 'EOF'
#!/bin/bash
echo "🔄 Deploy completado, configurando cron job..."
./deploy_setup_cron.sh
EOF

chmod +x .git/hooks/post-merge
```

### **Opción 2: Integración con CI/CD**

#### **GitHub Actions:**
```yaml
# .github/workflows/deploy.yml
name: Deploy to Production
on:
  push:
    branches: [main]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      
      - name: Deploy to server
        uses: appleboy/ssh-action@v0.1.4
        with:
          host: ${{ secrets.HOST }}
          username: ${{ secrets.USERNAME }}
          key: ${{ secrets.KEY }}
          script: |
            cd /ruta/al/proyecto
            git pull origin main
            composer install --no-dev --optimize-autoloader
            php artisan config:cache
            php artisan route:cache
            php artisan view:cache
            ./deploy_setup_cron.sh
```

#### **GitLab CI:**
```yaml
# .gitlab-ci.yml
deploy_production:
  stage: deploy
  script:
    - ssh usuario@servidor "cd /ruta/al/proyecto && git pull origin main"
    - ssh usuario@servidor "cd /ruta/al/proyecto && composer install --no-dev"
    - ssh usuario@servidor "cd /ruta/al/proyecto && php artisan config:cache"
    - ssh usuario@servidor "cd /ruta/al/proyecto && ./deploy_setup_cron.sh"
  only:
    - main
```

### **Opción 3: Script de Deploy Personalizado**

#### **deploy.sh:**
```bash
#!/bin/bash
# Script completo de deploy

echo "🚀 Iniciando deploy..."

# 1. Actualizar código
git pull origin main

# 2. Instalar dependencias
composer install --no-dev --optimize-autoloader

# 3. Limpiar y cachear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. Configurar cron job automáticamente
./deploy_setup_cron.sh

# 5. Verificar estado
php artisan about

echo "✅ Deploy completado exitosamente!"
```

## 🌐 **Plataformas Específicas**

### **Heroku:**
```bash
# En Procfile
web: vendor/bin/heroku-php-apache2 public/
release: php artisan migrate --force && php artisan config:cache

# En app.json
{
  "scripts": {
    "postdeploy": "php artisan sales:reset-daily"
  }
}
```

### **DigitalOcean App Platform:**
```yaml
# .do/app.yaml
name: tu-app
services:
  - name: web
    source_dir: /
    github:
      repo: usuario/repo
      branch: main
    run_command: php artisan serve --host 0.0.0.0 --port $PORT
    post_deploy:
      - php artisan config:cache
      - ./deploy_setup_cron.sh
```

### **AWS Elastic Beanstalk:**
```yaml
# .ebextensions/01_cron.config
files:
  "/etc/cron.d/sales_reset":
    mode: "000644"
    owner: root
    group: root
    content: |
      0 0 * * * cd /var/app/current && php artisan sales:reset-daily >> /dev/null 2>&1

commands:
  01_remove_old_cron:
    command: "rm -f /tmp/crontab || true"
  02_add_new_cron:
    command: "crontab /etc/cron.d/sales_reset"
```

### **Docker:**
```dockerfile
# Dockerfile
FROM php:8.1-fpm

# ... resto de configuración ...

# Copiar script de deploy
COPY deploy_setup_cron.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/deploy_setup_cron.sh

# Script de entrada
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]
```

```bash
# docker-entrypoint.sh
#!/bin/bash
set -e

# Configurar cron job al iniciar el contenedor
./deploy_setup_cron.sh

# Ejecutar comando original
exec "$@"
```

## 🔄 **Verificación Automática**

### **Health Check Script:**
```bash
#!/bin/bash
# health_check.sh

echo "🔍 Verificando estado del sistema..."

# Verificar que el cron job esté configurado
if crontab -l | grep -q "sales:reset-daily"; then
    echo "✅ Cron job configurado"
else
    echo "❌ Cron job NO configurado"
    exit 1
fi

# Verificar que el comando funcione
if php artisan sales:reset-daily --help > /dev/null 2>&1; then
    echo "✅ Comando disponible"
else
    echo "❌ Comando NO disponible"
    exit 1
fi

# Verificar logs recientes
if [ -f "storage/logs/laravel.log" ]; then
    echo "✅ Logs disponibles"
    echo "📝 Últimas entradas:"
    tail -5 storage/logs/laravel.log
else
    echo "❌ Logs NO disponibles"
fi

echo "🎉 Sistema funcionando correctamente!"
```

## 📱 **Notificaciones Automáticas**

### **Slack/Discord:**
```bash
# En deploy_setup_cron.sh, agregar:
SLACK_WEBHOOK="https://hooks.slack.com/services/TU/WEBHOOK/URL"

curl -X POST -H 'Content-type: application/json' \
  --data "{\"text\":\"🚀 Deploy completado en $(hostname)\n✅ Cron job configurado para Ventas por Día\"}" \
  $SLACK_WEBHOOK
```

### **Email:**
```bash
# Enviar email de confirmación
echo "Deploy completado exitosamente" | mail -s "Deploy Completado" admin@tuempresa.com
```

## 🚨 **Solución de Problemas**

### **Si el cron job no se configura:**
```bash
# Verificar permisos
ls -la deploy_setup_cron.sh

# Verificar que el usuario tenga permisos de crontab
sudo usermod -a -G crontab www-data

# Verificar logs del sistema
tail -f /var/log/syslog | grep cron
```

### **Si el comando no funciona:**
```bash
# Verificar que Laravel esté funcionando
php artisan about

# Verificar logs
tail -f storage/logs/laravel.log

# Verificar permisos de storage
chmod -R 775 storage/
chown -R www-data:www-data storage/
```

## 📚 **Resumen de Archivos**

- `deploy_setup_cron.sh` - Script principal de configuración
- `DEPLOY_AUTOMATION.md` - Esta documentación
- `.git/hooks/post-merge` - Hook para deploy automático
- `.github/workflows/deploy.yml` - GitHub Actions
- `.gitlab-ci.yml` - GitLab CI
- `docker-entrypoint.sh` - Script de entrada para Docker

## 🎯 **Recomendación Final**

Para la mayoría de casos, usa la **Opción 1** con el script `deploy_setup_cron.sh` que se ejecute después de cada `git pull`. Es simple, confiable y funciona en cualquier servidor Linux. 