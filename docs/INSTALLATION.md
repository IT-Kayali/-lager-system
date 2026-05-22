# Installation

Diese Datei beschreibt die Installation des Lagerverwaltungssystems auf einem neuen Server.

## Voraussetzungen

- Ubuntu Server
- PHP-FPM
- Composer
- MySQL oder MariaDB
- Node.js und npm
- Nginx
- Git

## Projekt klonen

cd /var/www
git clone git@github.com:IT-Kayali/-lager-system.git lager-system
cd /var/www/lager-system

## Abhängigkeiten installieren

composer install --no-dev --optimize-autoloader
npm install
npm run build

## .env erstellen

cp .env.example .env
php artisan key:generate
nano .env

Wichtige Werte:

APP_NAME="Lagerverwaltung"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://DEINE-DOMAIN-ODER-IP

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lager_system
DB_USERNAME=lager_user
DB_PASSWORD=DEIN_PASSWORT

## Datenbank erstellen

sudo mysql

Dann in MySQL ausführen:

CREATE DATABASE lager_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'lager_user'@'localhost' IDENTIFIED BY 'DEIN_PASSWORT';
GRANT ALL PRIVILEGES ON lager_system.* TO 'lager_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

## Migrationen ausführen

php artisan migrate --force
php artisan storage:link

## Rechte setzen

sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

## Cache für Produktion

php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

## Nginx und PHP-FPM neu laden

sudo nginx -t
sudo systemctl reload nginx
sudo systemctl restart php8.5-fpm
