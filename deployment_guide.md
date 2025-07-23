# 🚀 CnoT Deployment Commands

Guide for deploying and managing the CnoT application.

## �️ Automatic Versioning System

### How it Works
When a push is made to the `main` branch, GitHub Actions automatically:

1. **Generates a timestamp** in `YYYYMMDDHHMM` format (e.g., `202501241530` for January 24, 2025 at 3:30 PM)
2. **Tags the Docker image** with this timestamp instead of the Git SHA
3. **Deploys the application** with the new version
4. **Creates a `version.txt` file** containing the timestamp on the server

### User Interface Display
- The text "Last deployment" has been replaced with "**Version**"
- The timestamp is formatted in a readable way (e.g., `24/01/2025 15:30`)
- In development mode, displays "Development" instead of a timestamp

### Version Format
- **Raw format**: `YYYYMMDDHHMM` (e.g., `202501241530`)
- **Display format**: `DD/MM/YYYY HH:MM` (e.g., `24/01/2025 15:30`)
- **Development**: `Development`

### Modified Files
- `.github/workflows/production-deployment.yml`: Timestamp generation and deployment
- `src/functions.php`: `getDeploymentVersion()` function to read and format version
- `src/index.php`: Version display instead of "last deployment"
- `src/version.txt`: File containing current version (automatically created in production)

## �🏗️ Development vs Production Architecture

### Development Workflow (Branch: `dev`)
The development environment uses a **live reload** approach:

1. **Base Image**: The Dockerfile always copies code into the image:
   ```dockerfile
   COPY src/ /var/www/html/
   ```
   → This ensures correct permissions (www-data) and file structure

2. **Volume Override**: The volume `./src:/var/www/html` **OVERWRITES** the copied content:
   → Local code replaces the image code
   → Any local modification is **IMMEDIATELY** visible in the container
   → No rebuild needed: edit file → refresh browser!

3. **Live Development**:
   ```bash
   # Start dev environment
   docker compose -p cnot-dev --env-file .env.dev -f docker-compose.yml -f docker-compose-dev.yml up -d
   
   # Edit files in ./src → changes are instant!
   ```

### Production Workflow (Branch: `main`)
The production environment uses **immutable images**:

1. **Image Build**: Code is permanently copied into the Docker image
2. **Registry Push**: Immutable image pushed to Docker Hub (`timpoz/cnot`)
3. **Production Deploy**: Fixed, versioned, secure image deployed

### Key Differences:
- **DEV**: Local code mounted as volume (live reload)
- **PROD**: Code frozen in image (immutable, secure)

### Benefits:
- ✅ **Correct permissions** (thanks to initial copy)
- ✅ **Instant live reload** (thanks to volume override)
- ✅ **Same environment** dev/prod (same Dockerfile)
- ✅ **Production security** (immutable images)

## 📦 Installation

### Production (Registry)
```bash
DOCKER_IMAGE=timpoz/cnot:latest docker compose -f docker-compose.yml -f docker-compose-reverse-proxy.yml up -d
```

### Development
```bash
docker compose -p cnot-dev --env-file .env.dev -f docker-compose.yml -f docker-compose-dev.yml -f docker-compose-reverse-proxy-dev.yml up -d --build
```

## 🔄 Production Updates

### Option 1: Latest stable version
```bash
DOCKER_IMAGE=timpoz/cnot:latest docker compose -f docker-compose.yml -f docker-compose-reverse-proxy.yml up -d --force-recreate
```

### Option 2: Specific version
```bash
# Replace TIMESTAMP_HERE with the desired timestamp (YYYYMMDDHHMM format)
DOCKER_IMAGE=timpoz/cnot:TIMESTAMP_HERE docker compose -f docker-compose.yml -f docker-compose-reverse-proxy.yml up -d --force-recreate
```

### Option 3: Build from source (backup method)
```bash
git checkout main
cd /root/cnot/cnot
git pull
docker compose -f docker-compose.yml -f docker-compose-reverse-proxy.yml build --no-cache
docker compose -f docker-compose.yml -f docker-compose-reverse-proxy.yml up -d --force-recreate
git checkout dev
```

## ⏪ Rollback

### Automatic rollback
```bash
./rollback.sh
```

### Rollback to specific version
```bash
./rollback.sh <TIMESTAMP>
```

## 📋 Image Management

### List available images on Docker Hub
```bash
curl -s "https://hub.docker.com/v2/repositories/timpoz/cnot/tags/" | jq -r '.results[] | .name' | head -10
```

### List local images
```bash
docker images | grep cnot
```

### Clean up unused images
```bash
docker image prune -f
```

## 🔍 Monitoring

### View running containers
```bash
docker ps | grep cnot
```

### View webserver logs
```bash
docker logs cnot-webserver-1 -f
```

### Check service status
```bash
docker compose -f docker-compose.yml -f docker-compose-reverse-proxy.yml ps
```

## 🏷️ Image Tags

### Tag structure (updated with versioning system)
- **Production**: `timpoz/cnot:TIMESTAMP` and `timpoz/cnot:latest`
- **Timestamp format**: `YYYYMMDDHHMM` (e.g., `202501241530`)

### Examples
```bash
# Production images with timestamp versioning
timpoz/cnot:202501241530
timpoz/cnot:202501251045
timpoz/cnot:latest
```

## 🔧 Troubleshooting

### Force pull new image
```bash
docker pull timpoz/cnot:latest
DOCKER_IMAGE=timpoz/cnot:latest docker compose -f docker-compose.yml -f docker-compose-reverse-proxy.yml up -d --force-recreate
```

### Complete rebuild
```bash
docker compose -f docker-compose.yml -f docker-compose-reverse-proxy.yml down
docker compose -f docker-compose.yml -f docker-compose-reverse-proxy.yml up -d --build --force-recreate
```

### Check database connectivity
```bash
docker exec cnot-webserver-1 ping cnot-database-1
```

## 📚 GitHub Actions Workflows

- **Push to `dev`** → Creates PR to `main` (no image build)
- **Merge to `main`** → Build image with `TIMESTAMP` + `latest` tags + Automatic production deployment

### Workflow optimization
- **Timestamp versioning**: Images tagged with `YYYYMMDDHHMM` format for easy identification
- **No more dev images**: Saves GitHub Actions minutes and Docker Hub storage
- **Production only**: Images are built only when code is ready for production
- **Version tracking**: Automatic version display in the application interface

---

> 💡 **Tip**: Always use `DOCKER_IMAGE=` to specify the registry image, otherwise Docker Compose will use the local image.
