#!/bin/sh
# wait-for-opensearch.sh - Wait for OpenSearch to be ready before executing setup

set -e

host="${OPENSEARCH_HOST:-opensearch_container}"
port="${OPENSEARCH_PORT:-9200}"
max_tries=30
count=0

echo "⏳ Waiting for OpenSearch to be ready on ${host}:${port}..."

# Wait for OpenSearch port to be available (checking from inside php_container)
until docker compose exec -T php_container nc -z "$host" "$port" 2>/dev/null; do
  count=$((count + 1))
  if [ $count -gt $max_tries ]; then
    echo "❌ OpenSearch did not become ready in time"
    exit 1
  fi
  echo "  Attempt $count/$max_tries: OpenSearch is unavailable - sleeping"
  sleep 2
done

echo "✅ OpenSearch port is open! Waiting an extra 3 seconds for full initialization..."
sleep 3

echo "✅ OpenSearch is ready!"


