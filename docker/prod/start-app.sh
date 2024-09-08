#!/bin/sh

cd /var/www/

# Sets APP_KEY if not present
if [ -z $APP_KEY ]; then
    export APP_KEY=$(php artisan key:generate --show);
fi

# Run Migrations DB
php artisan migrate --force

# Run Migrations DB
php artisan db:seed --force

# Optimize the Application
php artisan optimize

# Cache the Config for Performance
php artisan config:cache

# Cache the Routes for Performance
php artisan storage:link
