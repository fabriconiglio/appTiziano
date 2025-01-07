#!/bin/bash

# Generar key de la aplicación si no existe
php artisan key:generate --force

# Ejecutar migraciones
php artisan migrate --force

# Iniciar el servidor
php artisan serve --host=0.0.0.0 --port=$PORT
