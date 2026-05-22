# Wartung und Updates

Diese Datei beschreibt Wartung, Updates, Backups und Sicherheitschecks.

## Status prüfen

cd /var/www/lager-system
git status
git log --oneline -5

## Änderungen speichern

git status --short
git add .
git commit -m "Beschreibung der Änderung"
git push origin main

## Update vom Repository ziehen

cd /var/www/lager-system

git pull origin main
composer install --no-dev --optimize-autoloader
npm install
npm run build

php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

sudo nginx -t
sudo systemctl reload nginx
sudo systemctl restart php8.5-fpm

## Datenbank-Backup

mysqldump -u lager_user -p lager_system > ~/lager_system_backup_$(date +%F_%H-%M).sql

## Uploads sichern

tar -czf ~/lager_uploads_backup_$(date +%F_%H-%M).tar.gz /var/www/lager-system/storage/app/public

## Logs prüfen

tail -n 120 storage/logs/laravel.log
sudo tail -n 80 /var/log/nginx/lager-system-error.log
sudo systemctl status php8.5-fpm --no-pager

## Sicherheitscheck vor Push

git ls-files .env || true
git ls-files | grep -E "^(.env|vendor/|node_modules/|storage/app/public/.*|public/storage|public/build|.*\.sql$)" || true

Wenn .env, Uploads, SQL-Dumps oder Build-Dateien angezeigt werden, nicht pushen.
