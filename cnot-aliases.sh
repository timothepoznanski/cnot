#!/bin/bash

# CnoT Project Aliases
# Simple aliases for managing development and production environments

# Development environment
alias cnot-dev='CNOT_DIR=$(cd "$(dirname "${BASH_SOURCE[0]}" 2>/dev/null || echo ".")" && pwd) && \
    cd "$CNOT_DIR" && \
    docker compose down 2>/dev/null; docker compose -p cnot-dev down 2>/dev/null && \
    git checkout dev && git pull origin dev && \
    docker compose -p cnot-dev \
        --env-file .env --env-file .env.dev \
        -f docker-compose.yml \
        -f docker-compose-dev.yml \
        -f docker-compose-reverse-proxy-dev.yml \
        up -d --build'

# Production environment  
alias cnot-prod='CNOT_DIR=$(cd "$(dirname "${BASH_SOURCE[0]}" 2>/dev/null || echo ".")" && pwd) && \
    cd "$CNOT_DIR" && \
    docker compose down 2>/dev/null; docker compose -p cnot-dev down 2>/dev/null && \
    git checkout main && git pull origin main && \
    docker compose --env-file .env --env-file .env.prod \
        -f docker-compose.yml \
        -f docker-compose-reverse-proxy.yml \
        build --no-cache && \
    docker compose --env-file .env --env-file .env.prod \
        -f docker-compose.yml \
        -f docker-compose-reverse-proxy.yml \
        up -d'
