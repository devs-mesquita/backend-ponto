#!/bin/bash
php artisan key:generate && \
php artisan jwt:secret && \
php artisan config:cache &&\
php artisan migrate --force && \
apache2ctl -D FOREGROUND