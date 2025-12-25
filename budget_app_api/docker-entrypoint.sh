#!/bin/sh
set -e

# Fix permissions for Symfony cache and logs
chown -R www-data:www-data /var/www/var
chmod -R 775 /var/www/var

# Execute the main command (Apache)
exec "$@"