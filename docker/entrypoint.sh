#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

echo "[entrypoint] Starte Hey, Alter! Essen Container ..."

# 1) APP_KEY beschaffen und in der container-internen .env verankern
#    Wir lesen den Key entweder aus einem persistenten File im storage-Volume
#    oder generieren ihn einmalig neu. Anschliessend schreiben wir ihn in die
#    .env im Container, damit Laravel ihn ueber phpdotenv liest. Diese Variante
#    funktioniert auch fuer "docker compose exec app php artisan ..."-Aufrufe,
#    weil die per docker-compose gesetzten OS-Vars APP_KEY nicht ueberschreiben.
KEY_FILE=/var/www/html/storage/app_key

if [ ! -f .env ]; then
    cp .env.example .env
    echo "[entrypoint] .env aus .env.example erzeugt."
fi

if [ -s "$KEY_FILE" ]; then
    APP_KEY=$(cat "$KEY_FILE")
    echo "[entrypoint] APP_KEY aus persistiertem Schluesselfile ${KEY_FILE} geladen."
elif [ -z "${APP_KEY:-}" ] || [ "${APP_KEY}" = "base64:" ]; then
    APP_KEY=$(php -r "echo 'base64:' . base64_encode(random_bytes(32));")
    (umask 077 && printf '%s' "$APP_KEY" > "$KEY_FILE")
    chown www-data:www-data "$KEY_FILE" 2>/dev/null || true
    echo "[entrypoint] === NEUER APP_KEY generiert und persistiert ==="
    echo "[entrypoint] Persistiert in: $KEY_FILE (im app_storage-Volume)"
    echo "[entrypoint] Empfehlung: zusaetzlich in der Host-.env hinterlegen, falls"
    echo "[entrypoint] das Volume mal geloescht wird:"
    echo "[entrypoint]   $APP_KEY"
    echo "[entrypoint] ==============================================="
else
    # APP_KEY kam aus OS-Env (Host hat ihn gesetzt) — persistieren
    (umask 077 && printf '%s' "$APP_KEY" > "$KEY_FILE")
    chown www-data:www-data "$KEY_FILE" 2>/dev/null || true
fi

# In die container-interne .env schreiben (idempotent)
if grep -qE '^APP_KEY=' .env; then
    # Existierende Zeile ersetzen
    sed -i "s|^APP_KEY=.*|APP_KEY=${APP_KEY}|" .env
else
    printf '\nAPP_KEY=%s\n' "$APP_KEY" >> .env
fi
export APP_KEY

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
