#!/bin/sh
# Prépare les dossiers d'état et les droits GPIO, puis lance la commande (Apache, planificateur ou service GPIO).
set -e
cd /var/www/html/aqua
mkdir -p var/state var/status var/backup var/log var/cache
chown -R www-data:www-data var/state var/status var/backup var/log var/cache 2>/dev/null || true

# Accès aux GPIO (Raspberry) : groupe propriétaire de /dev/gpiochip0 donné à www-data
for dev in /dev/gpiochip0 /dev/gpiomem; do
    if [ -e "$dev" ]; then
        gid=$(stat -c %g "$dev")
        getent group "$gid" >/dev/null || groupadd -g "$gid" gpio-host
        usermod -aG "$(getent group "$gid" | cut -d: -f1)" www-data
    fi
done

# Planificateur et service GPIO : tournent sous www-data (mêmes droits fichiers qu'Apache, accès GPIO via le groupe)
if [ "$1" = "php" ] || [ "$1" = "python3" ]; then
    exec setpriv --reuid=www-data --regid=www-data --init-groups "$@"
fi
exec "$@"
