#!/bin/sh

echo "Waiting for database to be ready..."
until nc -z mariadb 3306; do
  echo "Database not ready yet, sleeping..."
  sleep 1
done

echo "Database is ready. Running migrations..."

php bin/console doctrine:database:drop --force || true
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate --no-interaction

exec php-fpm