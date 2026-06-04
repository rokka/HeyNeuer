# Hey, Alter! Essen — Computerverwaltung

Webanwendung zur Verwaltung der gespendeten und aufgearbeiteten Computer von **Hey, Alter! Essen** (gemeinnütziger Verein, der Computer für bedürftige Schüler aufbereitet).

## Funktionsumfang

- **Benutzerverwaltung** mit Name, E-Mail, Passwort, Registrierungs- und letztem Login-Datum.
- **Einladungs-Workflow**: nur Administratoren legen neue Benutzer per E-Mail an; der Eingeladene setzt über einen signierten Link Passwort und Namen.
- **Selbst-Registrierung** über einen kryptischen Link (Token in `.env`). Selbst registrierte Konten sind nie Admin; alle Admins werden per E-Mail informiert.
- **Startseite** mit den 10 zuletzt angelegten Computern und einem Balkendiagramm der Neuzugänge der letzten 12 Wochen (Chart.js).
- **Computer-CRUD** mit Suche und Filtern (Status, Geräteklasse), automatisch vergebener eindeutiger Nummer im Format `HA-E-0001`. Vorbelegung per Query-Parameter (`/computers/create?model=…&type_name=laptop&hard_drive_type=2&…`).
- **Ausgabe** (`/distributions`): Liste aller abgegebenen Geräte und Anlage neuer Abgaben. Vor-/Nachname und Geburtsdatum werden **nicht** gespeichert — nur ein SHA-256-Hash dient zur Erkennung von Doppelvergaben. Computernummer-Eingabe per Tippen oder QR-Scan (Kamera).
- **Statistik** als Kreuztabelle Status × Geräteklasse, alle Zellen und Summen verlinken in die Computerliste mit gesetztem Filter.
- **Audit-Log** für Änderungen an Computern (Spatie laravel-activitylog) mit deutscher Beschriftung, inkl. Spezialmeldung „Status von … auf … geändert".
- **Passwort vergessen / Zurücksetzen** über die Laravel-Breeze-Funktion.
- **Berechtigungen**: alle angemeldeten Benutzer dürfen Computer anlegen und bearbeiten; nur Administratoren dürfen löschen sowie Benutzer verwalten.

Oberfläche und alle Texte: **Deutsch**. Zeitzone: **Europe/Berlin**.

## Tech-Stack

| Bereich | Technologie |
|---|---|
| Framework | Laravel 13 |
| PHP | 8.4 im Docker-Image (≥ 8.2 reicht) |
| Frontend | Blade + Livewire 3 + Alpine.js (via Laravel Breeze Livewire-Flavor) |
| Build | Vite + Tailwind CSS 4 |
| Datenbank | MySQL 8 (Produktion) · SQLite (Tests, in-memory) |
| Charts | Chart.js 4 |
| QR-Scan | html5-qrcode |
| Audit-Log | spatie/laravel-activitylog 5 |

## Setup mit Docker (empfohlen)

### Voraussetzungen
- Docker Desktop ≥ 4.x bzw. Docker Engine ≥ 24 mit Compose v2
- ~1 GB freier Plattenplatz für Images

### Erststart in 3 Schritten

```bash
# 1. .env aus Vorlage erzeugen und Werte eintragen
cp .env.example .env
# In .env mindestens setzen:
#   APP_KEY=               (leer lassen — wird beim ersten Start erzeugt)
#   DB_PASSWORD=<starkes-passwort>
#   MAIL_MAILER=smtp + Mail-Daten (oder 'log' für lokales Testing)
# Optional:
#   SELF_REGISTRATION_TOKEN=<langer-zufalls-string>

# 2. Container bauen und hochfahren
docker compose up -d --build

# 3. Logs ansehen (Migrate + Seed laufen automatisch beim ersten Start)
docker compose logs -f app
```

Anwendung ist anschließend unter **http://localhost:8000** erreichbar.

**Initial-Login** (per Seed angelegt — Passwort sofort über `/profile` ändern):
- E-Mail: `admin@heyneuer.com` (überschreibbar via `SEED_ADMIN_EMAIL`)
- Passwort: `changeme!` (überschreibbar via `SEED_ADMIN_PASSWORD`)

### Alltagsbefehle

```bash
# Container-Status / Logs
docker compose ps
docker compose logs -f app
docker compose logs -f db

# Eine Shell im App-Container öffnen
docker compose exec app bash

# Artisan-Befehle ausführen
docker compose exec app php artisan migrate
docker compose exec app php artisan auth:rotate-self-register-token
docker compose exec app php artisan tinker

# Tests ausführen (gegen SQLite in-memory)
docker compose exec app php artisan test

# Frontend-Build nach Template-Änderungen
docker compose exec app npm run build

# Stop / Start
docker compose stop
docker compose start

# Komplett abbauen (DB-Volume bleibt erhalten)
docker compose down

# WIRKLICH alles weg, inkl. DB-Volume:
docker compose down -v
```

### Was die Container tun
| Service | Image | Beschreibung |
|---|---|---|
| `app` | gebaut aus [Dockerfile](Dockerfile) — PHP 8.4 + Apache + Composer + Node | Liefert die Anwendung auf Port 80 aus; Entrypoint-Skript wartet auf MySQL, führt Migrate + Seed aus, optimiert Caches |
| `db` | `mysql:8.0` | MySQL-Datenbank, Daten liegen im Named-Volume `db_data` |

DB-Port 3306 ist **nicht** nach außen exportiert (interne Compose-Kommunikation über den Hostnamen `db`). Wer mit einem GUI-Tool wie DBeaver zugreifen will, entfernt den Kommentar im `ports:`-Block der `docker-compose.yml` und mappt einen freien Host-Port.

### Konfiguration über .env

`docker-compose.yml` liest alle relevanten Variablen aus der lokalen `.env`. Die wichtigsten Schalter:

| Variable | Default | Bedeutung |
|---|---|---|
| `APP_PORT` | `8000` | Port auf dem Host, der nach Container-Port 80 geforwarded wird |
| `APP_ENV` | `local` | `production` aktiviert Config-/Route-/View-Cache automatisch |
| `APP_KEY` | leer | Beim ersten Start automatisch erzeugt |
| `APP_DEBUG` | `true` | In Produktion auf `false` setzen |
| `APP_URL` | `http://localhost:8000` | externe URL (HTTPS in Produktion!) |
| `DB_PASSWORD` | `changeme` | Passwort des App-Users in MySQL — bitte ändern |
| `DB_ROOT_PASSWORD` | `rootchangeme` | Root-Passwort von MySQL |
| `MAIL_*` | s. `.env.example` | SMTP-Daten für Einladungs-Mails |
| `INVITATION_EXPIRY_HOURS` | `72` | Gültigkeit der signierten Einladungs-Links |
| `SELF_REGISTRATION_TOKEN` | leer | Token für `/register/<token>`. Leer = Funktion deaktiviert |
| `SEED_ADMIN_EMAIL` / `SEED_ADMIN_PASSWORD` | siehe oben | Initialer Admin beim ersten Start |

### Produktions-Hinweise

- `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://…` setzen
- Hinter einem Reverse-Proxy (Nginx / Traefik / Caddy) terminieren, der HTTPS macht
- Image bauen ohne lokalen Cache: `docker compose build --no-cache app`
- DB-Volume regelmäßig sichern: `docker compose exec db mysqldump -u root -p"$DB_ROOT_PASSWORD" --all-databases > backup.sql`
- Updates: Code pullen → `docker compose up -d --build app` (Migrate + Seed laufen idempotent automatisch)

## Alternative: Manuelles Setup ohne Docker

### Voraussetzungen
- PHP ≥ 8.2 mit Extensions: `openssl`, `pdo_mysql`, `pdo_sqlite`, `mbstring`, `fileinfo`, `curl`, `intl`, `zip`, `gd`, `sodium`, `sockets`
- Composer ≥ 2.x
- Node.js ≥ 20 / npm ≥ 10
- MySQL 8.x (lokal oder remote)

### Installation

```powershell
# 1. PHP-Abhängigkeiten
composer install

# 2. JS-Abhängigkeiten
npm install

# 3. .env anlegen
copy .env.example .env
php artisan key:generate

# In .env mindestens setzen:
#   DB_DATABASE=hey_alter_essen
#   DB_USERNAME=hey_alter
#   DB_PASSWORD=<App-User-Passwort>
#   MAIL_MAILER=smtp + Mail-Daten

# 4. Datenbank vorbereiten:
#   CREATE DATABASE hey_alter_essen CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
#   CREATE USER 'hey_alter'@'localhost' IDENTIFIED BY '<passwort>';
#   GRANT ALL PRIVILEGES ON hey_alter_essen.* TO 'hey_alter'@'localhost';

# 5. Migrations + Seed
$env:SEED_ADMIN_EMAIL='admin@heyneuer.com'
$env:SEED_ADMIN_PASSWORD='changeme!'
php artisan migrate --seed

# 6. Assets bauen
npm run build

# 7. Dev-Server starten
php artisan serve
# Anwendung läuft unter http://127.0.0.1:8000
```

### Entwicklungs-Workflow

```powershell
# Vite HMR
npm run dev

# Tests
php artisan test
```

## Tests

```bash
# In Docker
docker compose exec app php artisan test

# Manuell
php artisan test
```

Aktueller Stand: **83 Tests · 297 Assertions · alle grün.** Tests laufen gegen SQLite in-memory (siehe `phpunit.xml`).

Abdeckung u.a.:
- `InvitationFlowTest` — Einladung anlegen, akzeptieren, Token-Validierung, Berechtigungsschutz
- `SelfRegistrationTest` — kryptischer Link, Admin-Schutz, Mail an alle Admins
- `ComputerCrudTest` — Anlegen mit auto-generierter Nummer, Update, Löschen (Admin-Schutz), Filter, Number-Generator-Sequenz, Query-Parameter-Vorbelegung, Activity-Log-Beschriftung
- `DistributionsIndexTest` + `DistributionsCreateTest` — Listen-Anzeige, Hash-Spezifikation, Computernummer-Normalisierung, Doppelvergabe-Schutz, URL-Pre-Fill, Datenschutz (Klartextdaten landen nicht in der DB)
- `UserEditTest` — Benutzer bearbeiten, Admin-Rolle-Selbstschutz, Einladung erneut senden, Löschen
- `StatisticsTest` — Matrix-Summen entsprechen Gesamtzahl
- `DashboardTest` — letzte Computer + 12-Wochen-Chart-Bucket
- `AdminGateTest` — Zugriffsschutz Admin-Routen
- `LoginTrackingTest` — `last_login_at` wird durch Login-Event aktualisiert
- `Auth\*` + `ProfileTest` — Standard-Auth-Flows

## Wichtige Konventionen

- **Eindeutige Computer-Nummer** wird über `App\Services\ComputerNumberGenerator` aus der `sequences`-Tabelle gezogen (Transaction + `lockForUpdate`). Format `HA-E-0001`, wächst bei > 9999 auf 5+ Stellen.
- **PHP-Enums** (`DeviceClass`, `ComputerStatus`, `DiskType`) liefern deutsche Labels über `->label()`. In Migrations als `string` gespeichert, im Model gecastet.
- **`User::name`** ist eine einzelne Spalte. Der Name wird im Profil-Formular als ein Eingabefeld erfasst.
- **Einladungslink** ist eine `URL::temporarySignedRoute`, Standardgültigkeit 72 Stunden (`INVITATION_EXPIRY_HOURS` in `.env`).
- **Empfänger-Hash für Ausgaben** wird gemäß Vorgabe gebildet: `hash('sha256', strtolower(firstname . "_" . lastname . "_" . birthday))`. Klartext-Personendaten werden nie persistiert.
- **Berechtigungen** über `Gate::define('admin', ...)` in `AppServiceProvider` plus `ComputerPolicy` und `UserPolicy`. Routen nutzen `can:admin` als Middleware.
- **`Computer::__set('oldAttributes', …)`** ist eine bewusste Override für eine Inkompatibilität zwischen `spatie/laravel-activitylog 5.0` und Laravel 13 — leitet das Property-Set in die typed Trait-Property statt in Eloquents Attribute-Bag um.

## Verzeichnisstruktur (Auszug)

```
app/
  Console/Commands/   RotateSelfRegisterTokenCommand.php
  Enums/              DeviceClass.php, ComputerStatus.php, DiskType.php
  Listeners/          UpdateLastLoginAt.php
  Livewire/
    Auth/             AcceptInvitation.php, SelfRegister.php
    Computers/        Index.php, Form.php
    Distributions/    Index.php, Create.php
    Statistics/       Matrix.php
    Users/            Index.php, InviteForm.php, Edit.php
    Dashboard.php
  Mail/               UserInvitationMail.php, NewSelfRegistrationMail.php
  Models/             Computer.php, Distribution.php, User.php, Sequence.php
  Policies/           ComputerPolicy.php, UserPolicy.php
  Services/           ComputerNumberGenerator.php
docker/
  entrypoint.sh       Wartet auf DB, Migrate, Cache, dann Apache starten
  php.ini             Memory-Limit, Timezone, Upload-Größen
database/
  factories/          UserFactory.php, ComputerFactory.php, DistributionFactory.php
  migrations/         users (erweitert), computers, sequences, activity_log, distributions
  seeders/            DatabaseSeeder.php
lang/de/              auth.php, validation.php, passwords.php, pagination.php
lang/de.json
resources/
  js/app.js           Chart.js + QR-Scanner + Alpine-Komponenten
  views/
    livewire/         alle Komponenten-Views
    mail/             user-invitation.blade.php, new-self-registration.blade.php
    layouts/          app.blade.php, guest.blade.php
routes/
  web.php             Auth + Computer + Statistik + Benutzer + Ausgabe + Self-Register
  auth.php            Login + Passwort-Reset (Registrierung entfernt)
tests/Feature/        Invitation-, Computer-, Statistics-, Dashboard-, AdminGate-,
                      LoginTracking-, SelfRegistration-, UserEdit-, Distributions-Tests
Dockerfile            Multi-stage Build (Composer + Vite + PHP-Apache)
docker-compose.yml    app + db, Healthcheck, persistente Volumes
.dockerignore         Schließt vendor, node_modules, .env etc. aus
```

## Bewusst nicht in v1

- Impressum / Datenschutzerklärung (interne Anwendung, vom Auftraggeber nicht gefordert).
- 2FA, E-Mail-Änderung mit Re-Verifizierung.
- Mehrsprachigkeit (nur Deutsch).
- Mehrmandantenfähigkeit / mehrere Standorte (im Gegensatz zur Vorgängerlösung explizit nicht gewünscht).
