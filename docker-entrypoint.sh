#!/bin/bash

# Verificar conexión a la base de datos
echo "Verificando conexión a la base de datos..."
max_retries=30
counter=0

while ! mysql -h"$DB_HOST" -u"$DB_USERNAME" -p"$DB_PASSWORD" --port="$DB_PORT" --protocol=TCP "$DB_DATABASE" -e "SELECT 1;" >/dev/null 2>&1; do
    counter=$((counter + 1))
    if [ $counter -gt $max_retries ]; then
        echo "Error: No se pudo conectar a la base de datos después de $max_retries intentos."
        exit 1
    fi
    echo "Intentando conectar a la base de datos... intento $counter de $max_retries"
    sleep 2
done

echo "Conexión a la base de datos establecida exitosamente"

# Generar key de la aplicación si no existe
php artisan key:generate --force

# Ejecutar migraciones
php artisan migrate --force

# Iniciar el servidor
php artisan serve --host=0.0.0.0 --port=$PORT
