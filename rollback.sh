#!/bin/bash

# Professional rollback script - v1.0 (Testing professional workflow)
# Usage: ./rollback.sh [version_sha]

set -e

ROLLBACK_TO=${1:-$(docker images --format "table {{.Tag}}" | grep -v latest | grep -v TAG | head -n 2 | tail -n 1)}

if [ -z "$ROLLBACK_TO" ]; then
    echo "❌ No previous version found for rollback"
    echo "Available versions:"
    docker images --format "table {{.Repository}}:{{.Tag}}\t{{.CreatedAt}}" | grep cnot
    exit 1
fi

echo "🔄 Rolling back to version: $ROLLBACK_TO"

# Update the image
docker tag cnot:$ROLLBACK_TO cnot:latest

# Restart services
docker compose -f docker-compose.yml -f docker-compose-reverse-proxy.yml up -d --force-recreate

echo "✅ Rollback completed to version: $ROLLBACK_TO"
echo "🔍 Current running version:"
docker ps --format "table {{.Image}}\t{{.Status}}\t{{.Ports}}" | grep cnot
