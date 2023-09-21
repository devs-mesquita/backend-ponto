 #!/bin/bash

php artisan key:generate    
php artisan migrate

apache2ctl -D FOREGROUND
