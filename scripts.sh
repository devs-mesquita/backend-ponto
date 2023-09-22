#!/bin/bash

php artisan key:generate && \
php artisan migrate --force && \
apache2ctl -D FOREGROUND