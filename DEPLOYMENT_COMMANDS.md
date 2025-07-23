# 🚀 CnoT Deployment Commands

Guide des commandes pour déployer et gérer l'application CnoT.

## 📦 Installation

### Production (Registry)
```bash
DOCKER_IMAGE=timpoz/cnot:latest docker compose -f docker-compose.yml -f docker-compose-reverse-proxy.yml up -d
```

### Développement
```bash
docker compose -p cnot-dev --env-file .env.dev -f docker-compose.yml -f docker-compose-dev.yml -f docker-compose-reverse-proxy-dev.yml up -d --build
```

## 🔄 Mise à jour Production

### Option 1: Dernière version stable
```bash
DOCKER_IMAGE=timpoz/cnot:latest docker compose -f docker-compose.yml -f docker-compose-reverse-proxy.yml up -d --force-recreate
```

### Option 2: Version spécifique
```bash
# Remplacer SHA_HERE par le commit SHA désiré
DOCKER_IMAGE=timpoz/cnot:main-SHA_HERE docker compose -f docker-compose.yml -f docker-compose-reverse-proxy.yml up -d --force-recreate
```

### Option 3: Build depuis les sources (méthode de secours)
```bash
git checkout main
cd /root/cnot/cnot
git pull
docker compose -f docker-compose.yml -f docker-compose-reverse-proxy.yml build --no-cache
docker compose -f docker-compose.yml -f docker-compose-reverse-proxy.yml up -d --force-recreate
git checkout dev
```

## ⏪ Rollback

### Rollback automatique
```bash
./rollback.sh
```

### Rollback vers une version spécifique
```bash
./rollback.sh <SHA_COMMIT>
```

## 📋 Gestion des images

### Lister les images disponibles sur Docker Hub
```bash
curl -s "https://hub.docker.com/v2/repositories/timpoz/cnot/tags/" | jq -r '.results[] | .name' | head -10
```

### Lister les images locales
```bash
docker images | grep cnot
```

### Nettoyer les images non utilisées
```bash
docker image prune -f
```

## 🔍 Monitoring

### Voir les conteneurs en cours
```bash
docker ps | grep cnot
```

### Voir les logs du webserver
```bash
docker logs cnot-webserver-1 -f
```

### Vérifier l'état des services
```bash
docker compose -f docker-compose.yml -f docker-compose-reverse-proxy.yml ps
```

## 🏷️ Tags des images

### Structure des tags
- **Dev**: `timpoz/cnot:dev-SHA` et `timpoz/cnot:dev-latest`
- **Production**: `timpoz/cnot:main-SHA` et `timpoz/cnot:latest`

### Exemples
```bash
# Image de développement
timpoz/cnot:dev-19cfb81
timpoz/cnot:dev-latest

# Image de production
timpoz/cnot:main-abc123  
timpoz/cnot:latest
```

## 🔧 Dépannage

### Forcer la récupération d'une nouvelle image
```bash
docker pull timpoz/cnot:latest
DOCKER_IMAGE=timpoz/cnot:latest docker compose -f docker-compose.yml -f docker-compose-reverse-proxy.yml up -d --force-recreate
```

### Reconstruire complètement
```bash
docker compose -f docker-compose.yml -f docker-compose-reverse-proxy.yml down
docker compose -f docker-compose.yml -f docker-compose-reverse-proxy.yml up -d --build --force-recreate
```

### Vérifier la connectivité à la base de données
```bash
docker exec cnot-webserver-1 ping cnot-database-1
```

## 📚 Workflows GitHub Actions

- **Push sur `dev`** → Build image `dev-SHA` + Création PR vers `main`
- **Merge vers `main`** → Build image `main-SHA` + Déploiement automatique en production

---

> 💡 **Astuce**: Utilisez toujours `DOCKER_IMAGE=` pour spécifier l'image du registry, sinon Docker Compose utilisera l'image locale.
