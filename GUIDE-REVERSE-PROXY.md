# 🚀 Guide de déploiement CnoT avec Reverse Proxy (NPM)

## 📋 Prérequis
- Docker et Docker Compose installés
- Nginx Proxy Manager (NPM) configuré et fonctionnel
- Accès aux commandes `cnot-*` configurées

## 🌐 1. Création des réseaux Docker

### Pour la PRODUCTION :
```bash
# Créer les réseaux externes pour la production
docker network create npm-cnot-webserver-net
docker network create npm-cnot-pma-net
docker network create cnot_internal-net
```

### Pour le DÉVELOPPEMENT :
```bash
# Créer les réseaux externes pour le développement
docker network create npm-cnot-dev-webserver-net
docker network create npm-cnot-dev-pma-net
docker network create cnot-dev_internal-net
```

## 🏭 2. Déploiement PRODUCTION

```bash
# Démarrer la production avec reverse proxy
cnot-prod

# OU manuellement :
cd /root/cnot/cnot
docker compose --env-file .env --env-file .env.prod \
    -f docker-compose.yml \
    -f docker-compose-reverse-proxy.yml \
    up -d --build --no-cache
```

**Ports internes :** 8077 (web), 8078 (phpMyAdmin)
**Réseaux utilisés :**
- `npm-cnot-webserver-net` : Connexion NPM → CnoT
- `npm-cnot-pma-net` : Connexion NPM → phpMyAdmin  
- `cnot_internal-net` : Communication interne entre services

## 🛠️ 3. Déploiement DÉVELOPPEMENT

```bash
# Démarrer le développement avec reverse proxy
cnot-dev

# OU manuellement :
cd /root/cnot/cnot
docker compose -p cnot-dev \
    --env-file .env --env-file .env.dev \
    -f docker-compose.yml \
    -f docker-compose-dev.yml \
    -f docker-compose-reverse-proxy-dev.yml \
    up -d --build
```

**Ports internes :** 8087 (web), 8088 (phpMyAdmin)
**Réseaux utilisés :**
- `npm-cnot-dev-webserver-net` : Connexion NPM → CnoT Dev
- `npm-cnot-dev-pma-net` : Connexion NPM → phpMyAdmin Dev
- `cnot-dev_internal-net` : Communication interne entre services dev

## ⚙️ 4. Configuration dans Nginx Proxy Manager

### Pour la PRODUCTION :
1. **Proxy Host CnoT :**
   - Domain: `cnot.votre-domaine.com`
   - Forward Hostname/IP: `cnot-webserver-1`
   - Forward Port: `80`
   - Websockets Support: ✅
   - Block Common Exploits: ✅

2. **Proxy Host phpMyAdmin :**
   - Domain: `pma-cnot.votre-domaine.com`
   - Forward Hostname/IP: `cnot-phpmyadmin-1`
   - Forward Port: `80`

### Pour le DÉVELOPPEMENT :
1. **Proxy Host CnoT Dev :**
   - Domain: `cnot-dev.votre-domaine.com`
   - Forward Hostname/IP: `cnot-dev-webserver-1`
   - Forward Port: `80`

2. **Proxy Host phpMyAdmin Dev :**
   - Domain: `pma-cnot-dev.votre-domaine.com`
   - Forward Hostname/IP: `cnot-dev-phpmyadmin-1`
   - Forward Port: `80`

## 🔧 5. Commandes utiles

```bash
# Vérifier l'état des environnements
cnot-status

# Vérifier les réseaux Docker
cnot-networks

# Arrêter tous les environnements
cnot-stop

# Voir les logs
cnot-logs

# Redémarrage rapide
cnot-restart

# Rebuild complet
cnot-rebuild

# Aide complète
cnot-help
```

## 🐛 6. Dépannage

### Vérifier les réseaux :
```bash
docker network ls | grep cnot
docker network inspect npm-cnot-webserver-net
```

### Vérifier les conteneurs :
```bash
docker ps --filter "name=cnot"
```

### Vérifier les logs :
```bash
# Production
docker compose logs webserver

# Développement  
docker compose -p cnot-dev logs webserver
```

### Recréer les réseaux si nécessaire :
```bash
# Supprimer (attention : arrêter les conteneurs avant)
docker network rm npm-cnot-webserver-net npm-cnot-pma-net cnot_internal-net
docker network rm npm-cnot-dev-webserver-net npm-cnot-dev-pma-net cnot-dev_internal-net

# Puis recréer avec les commandes de l'étape 1
```

## 📝 Notes importantes

- **Production** : Build sans cache (`--no-cache`) pour garantir la fraîcheur
- **Développement** : Build avec cache (`--build`) pour la rapidité + live reload
- **Séparation** : Chaque environnement a ses propres réseaux et ports
- **Données** : Les volumes persistent entre les redémarrages
- **SSL** : Configuré automatiquement par NPM avec Let's Encrypt

## 🔗 URLs d'accès

### Accès direct (sans reverse proxy) :
- **Production** : http://localhost:8077 (web), http://localhost:8078 (pma)
- **Développement** : http://localhost:8087 (web), http://localhost:8088 (pma)

### Accès via reverse proxy :
- **Production** : https://cnot.votre-domaine.com, https://pma-cnot.votre-domaine.com
- **Développement** : https://cnot-dev.votre-domaine.com, https://pma-cnot-dev.votre-domaine.com

---
*Généré le $(date) - Guide pour CnoT v2.0*
