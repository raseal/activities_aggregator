#!/bin/sh
# wait-for-rabbitmq.sh - Wait for RabbitMQ to be ready before executing setup

set -e

host="${RABBITMQ_HOST:-rabbitmq_container}"
port="${RABBITMQ_PORT:-5672}"
max_tries=30
count=0

echo "⏳ Waiting for RabbitMQ to be ready on ${host}:${port}..."

# Wait for RabbitMQ port to be available (checking from inside php_container)
until docker compose exec -T php_container nc -z "$host" "$port" 2>/dev/null; do
  count=$((count + 1))
  if [ $count -gt $max_tries ]; then
    echo "❌ RabbitMQ did not become ready in time"
    exit 1
  fi
  echo "  Attempt $count/$max_tries: RabbitMQ is unavailable - sleeping"
  sleep 2
done

echo "✅ RabbitMQ port is open! Waiting an extra 3 seconds for full initialization..."
sleep 3

echo "✅ RabbitMQ is ready!"


