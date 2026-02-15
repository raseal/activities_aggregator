#!/bin/sh

# Wait for MySQL to be ready for real connections
# This script checks if MySQL is accepting connections from external clients

set -e

MAX_ATTEMPTS=15
WAIT_SECONDS=3

echo "⏳ Waiting for MySQL to be ready for connections..."

for i in $(seq 1 $MAX_ATTEMPTS); do
  if docker compose exec php_container php -r "try { new PDO('mysql:host=mysql_container;dbname=ingestor', 'ingestor', '1234'); exit(0); } catch (Exception \$e) { exit(1); }" 2>/dev/null; then
    echo "✅ MySQL is up and accepting connections!"
    exit 0
  else
    echo "   Attempt $i/$MAX_ATTEMPTS: MySQL not ready yet, waiting $WAIT_SECONDS seconds..."
    sleep $WAIT_SECONDS
  fi
done

echo "❌ MySQL failed to become ready after $((MAX_ATTEMPTS * WAIT_SECONDS)) seconds"
exit 1

