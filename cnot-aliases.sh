#!/bin/bash

# CnoT Project Aliases
# This script defines convenient aliases for managing development and production environments

# Development environment alias
alias cnot-dev='CNOT_DIR=$(cd "$(dirname "${BASH_SOURCE[0]}" 2>/dev/null || echo ".")" && pwd) && \
    cd "$CNOT_DIR" && \
    echo "🔄 Stopping existing environments..." && \
    docker compose down 2>/dev/null; docker compose -p cnot-dev down 2>/dev/null && \
    echo "🛠️  Switching to development (dev branch)..." && \
    git checkout dev && \
    echo "🔄 Updating code from remote repository..." && \
    git pull origin dev && \
    echo "🚀 Starting development environment (build with cache for speed)..." && \
    docker compose -p cnot-dev \
        --env-file .env --env-file .env.dev \
        -f docker-compose.yml \
        -f docker-compose-dev.yml \
        -f docker-compose-reverse-proxy-dev.yml \
        up -d --build && \
    echo "✅ Development started on ports 8087 (web) and 8088 (phpmyadmin)" && \
    echo "🔥 Live reload enabled - your changes will be reflected immediately!" && \
    echo "🌐 Networks: npm-cnot-dev-webserver-net (DEV)"'

# Production environment alias
alias cnot-prod='CNOT_DIR=$(cd "$(dirname "${BASH_SOURCE[0]}" 2>/dev/null || echo ".")" && pwd) && \
    cd "$CNOT_DIR" && \
    echo "🔄 Stopping existing environments..." && \
    docker compose down 2>/dev/null; docker compose -p cnot-dev down 2>/dev/null && \
    echo "📦 Switching to production (main branch)..." && \
    git checkout main && \
    echo "🔄 Updating code from remote repository..." && \
    git pull origin main && \
    echo "🚀 Starting production environment (build without cache for freshness)..." && \
    docker compose --env-file .env --env-file .env.prod \
        -f docker-compose.yml \
        -f docker-compose-reverse-proxy.yml \
        build --no-cache && \
    docker compose --env-file .env --env-file .env.prod \
        -f docker-compose.yml \
        -f docker-compose-reverse-proxy.yml \
        up -d && \
    echo "✅ Production started on ports 8077 (web) and 8078 (phpmyadmin)" && \
    echo "🌐 Networks: npm-cnot-webserver-net (PROD)"'

# Status alias to check running containers
alias cnot-status='echo "📊 CnoT Environment Status:" && \
    echo "------------------------" && \
    docker ps --filter "name=cnot" --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}" && \
    echo "" && \
    echo "🔗 Networks:" && \
    docker network ls --filter "name=cnot" --format "table {{.Name}}\t{{.Driver}}"'

# Stop all CnoT environments
alias cnot-stop='CNOT_DIR=$(cd "$(dirname "${BASH_SOURCE[0]}" 2>/dev/null || echo ".")" && pwd) && \
    cd "$CNOT_DIR" && \
    echo "🛑 Stopping all CnoT environments..." && \
    docker compose down 2>/dev/null && \
    docker compose -p cnot-dev down 2>/dev/null && \
    echo "✅ All CnoT environments stopped"'

# Cleanup alias to remove unused Docker resources
alias cnot-cleanup='echo "🧹 Cleaning up Docker resources..." && \
    docker system prune -f && \
    docker volume prune -f && \
    echo "✅ Cleanup completed"'

# Help alias to show available commands
alias cnot-help='echo "🚀 CnoT Available Commands:" && \
    echo "-------------------------" && \
    echo "cnot-dev      - Start development environment (dev branch, ports 8087/8088)" && \
    echo "cnot-prod     - Start production environment (main branch, ports 8077/8078)" && \
    echo "cnot-status   - Show status of running containers and networks" && \
    echo "cnot-stop     - Stop all CnoT environments" && \
    echo "cnot-cleanup  - Clean up unused Docker resources" && \
    echo "cnot-help     - Show this help message" && \
    echo "" && \
    echo "📖 For more information, check the README.md file"'

echo "✅ CnoT aliases loaded successfully!"
echo "💡 Use 'cnot-help' to see available commands"
