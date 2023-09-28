#!/bin/bash

php artisan key:generate && \
php artisan migrate --force && \
php artisan jwt:secret && \
php artisan cache:clear &&\
apache2ctl -D FOREGROUND