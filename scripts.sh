#!/bin/bash

php artisan key:generate && \
php artisan migrate --force && \
php artisan jwt:secret && \
apache2ctl -D FOREGROUND