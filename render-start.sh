#!/bin/sh

# Exit immediately if a command exits with a non-zero status.
set -e

# Wait for the database to be ready by checking if the port is open.
echo "Waiting for database at $DB_HOST:$DB_PORT..."
while ! nc -z $DB_HOST $DB_PORT; do
  sleep 1
done
echo "Database is ready."

# Check if the database already has tables. If not, import the SQL file.
# The `mysql` command will exit with a non-zero status if the 'USE' or 'SHOW TABLES' fails.
# We check if the output of 'SHOW TABLES' has any lines (i.e., any tables exist).
if ! mysql -h $DB_HOST -u $DB_USER -p$DB_PASSWORD -e "USE $DB_NAME; SHOW TABLES;" 2>/dev/null | grep -q .; then
  echo "Database is empty. Importing data from /var/www/html/docker/mysql/library.sql..."
  # Import the .sql file. Note the path is inside the container.
  mysql -h $DB_HOST -u $DB_USER -p$DB_PASSWORD $DB_NAME < /var/www/html/docker/mysql/library.sql
  echo "Database import complete."
else
  echo "Database already contains data. Skipping import."
fi

# This is the original command to start the PHP server.
echo "Starting PHP-FPM..."
php-fpm
