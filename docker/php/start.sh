#!/bin/bash

# Iniciar cron en segundo plano
cron

# Iniciar PHP-FPM en primer plano
php-fpm

mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/storage
chmod -R 777 /var/www/html/storage

cron

php-fpm
