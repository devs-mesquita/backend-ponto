#!/bin/bash
php artisan key:generate && \
php artisan migrate --force && \
php artisan jwt:secret && \
php artisan config:cache &&\
apache2ctl -D FOREGROUND