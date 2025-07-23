# Install Prod (using registry image)
DOCKER_IMAGE=timpoz/cnot:latest docker compose -f docker-compose.yml -f docker-compose-reverse-proxy.yml up -d

# Install dev 
docker compose -p cnot-dev --env-file .env.dev -f docker-compose.yml -f docker-compose-dev.yml -f docker-compose-reverse-proxy-dev.yml up -d --build

# Update Prod (without using github workflow - manual deployment with specific version)
# Option 1: Use latest production image
DOCKER_IMAGE=timpoz/cnot:latest docker compose -f docker-compose.yml -f docker-compose-reverse-proxy.yml up -d --force-recreate

# Option 2: Use specific version (replace SHA with actual commit SHA)
DOCKER_IMAGE=timpoz/cnot:main-SHA_HERE docker compose -f docker-compose.yml -f docker-compose-reverse-proxy.yml up -d --force-recreate

# Option 3: Traditional build from source (backup method)
git checkout main
cd /root/cnot/cnot
git pull
docker compose -f docker-compose.yml -f docker-compose-reverse-proxy.yml build --no-cache
docker compose -f docker-compose.yml -f docker-compose-reverse-proxy.yml up -d --force-recreate
git checkout dev

# Rollback to previous version (use our rollback script)
./rollback.sh

# List available registry images
docker search timpoz/cnot || curl -s https://hub.docker.com/v2/repositories/timpoz/cnot/tags/ | jq '.results[].name'