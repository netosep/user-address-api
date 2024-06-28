#!/bin/bash

# run commands
composer install --quiet
php artisan key:generate --ansi --quiet
php artisan migrate --force --quiet

# console message
echo -e "\\n\\t\\e[42m\\e[97m APP IS READY! \\e[0m → \\e[44m\\e[97m http://localhost:8080 \\e[0m\\n"