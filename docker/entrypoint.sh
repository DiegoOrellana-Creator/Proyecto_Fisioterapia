#!/usr/bin/env bash
# Arranque del contenedor en Render: ajusta el puerto, prepara Laravel,
# migra la base de datos y levanta Apache.
set -e

PORT="${PORT:-10000}"
sed -i "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

if [ -z "${APP_KEY}" ]; then
    echo "ERROR: falta la variable APP_KEY. Genérala con: php artisan key:generate --show" >&2
    exit 1
fi

mkdir -p storage/framework/{cache/data,sessions,views} storage/logs bootstrap/cache

# Cachés de producción
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Tablas nuevas o cambiadas (no borra datos)
php artisan migrate --force

# Datos de demostración solo la primera vez (si SEED_DEMO=true y la base está vacía)
if [ "${SEED_DEMO:-false}" = "true" ]; then
    php artisan demo:sembrar
fi

# Las cachés se generaron como root; Apache trabaja como www-data
chown -R www-data:www-data storage bootstrap/cache

exec apache2-foreground
