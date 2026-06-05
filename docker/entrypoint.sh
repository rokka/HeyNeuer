#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

echo "[entrypoint] Starte Hey, Alter! Essen Container ..."

# 1) APP_KEY erzeugen, falls noch nicht gesetzt (z.B. erster Start ohne .env)
if [ -z "${APP_KEY:-}" ] || [ "${APP_KEY}" = "base64:" ]; then
    if [ ! -f .env ]; then
        cp .env.example .env
        echo "[entrypoint] .env aus .env.example erzeugt."
    fi
    if ! grep -q "^APP_KEY=base64:" .env 2>/dev/null; then
        php artisan key:generate --force
    fi
fi

# 2) Auf MySQL warten (max. 60 Sekunden). Wir nutzen PHP/PDO statt mysqladmin,
#    weil der Debian-default-mysql-client (MariaDB) standardmäßig TLS erzwingt
#    und am selbst-signierten MySQL-Zertifikat scheitert ("ERROR 2026: TLS/SSL
#    error: self-signed certificate in certificate chain"). Der PDO-Treiber
#    der App verwendet kein TLS by default — wenn der Ping hier klappt,
#    klappt auch die App-Verbindung.
if [ -n "${DB_HOST:-}" ]; then
    echo "[entrypoint] Warte auf MySQL bei ${DB_HOST}:${DB_PORT:-3306} ..."
    for i in $(seq 1 60); do
        if php -r "
            try {
                new PDO(
                    'mysql:host=' . getenv('DB_HOST') . ';port=' . (getenv('DB_PORT') ?: 3306),
                    getenv('DB_USERNAME'),
                    getenv('DB_PASSWORD'),
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 2]
                );
                exit(0);
            } catch (Throwable \$e) {
                exit(1);
            }
        " >/dev/null 2>&1; then
            echo "[entrypoint] MySQL ist bereit."
            break
        fi
        if [ "$i" -eq 60 ]; then
            echo "[entrypoint] FEHLER: MySQL nicht erreichbar nach 60s." >&2
            exit 1
        fi
        sleep 1
    done
fi

# 3) Migrate (force = ohne Rückfrage in Production) + Seed (idempotent)
echo "[entrypoint] Migrations + Seed ausführen ..."
php artisan migrate --force --seed

# 4) Symlink storage → public/storage anlegen (falls noch nicht vorhanden)
php artisan storage:link 2>/dev/null || true

# 5) In Production: Konfig + Routes + Views cachen
if [ "${APP_ENV:-local}" = "production" ]; then
    echo "[entrypoint] Production-Caches aufbauen ..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
else
    php artisan config:clear || true
    php artisan view:clear || true
fi

# 6) Berechtigungen für storage/bootstrap-cache nochmal sicherstellen
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

echo "[entrypoint] Setup fertig — starte: $*"
exec "$@"
