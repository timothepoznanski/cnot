# Configuration des Workflows GitHub Actions

Ce repository contient des workflows GitHub Actions pour automatiser le déploiement en production avec validation.

## Workflow de déploiement

### Approche avec Pull Request (sécurisée)
- **Déclencheur** : Push sur la branche `dev`
- **Action** : Création automatique d'une PR `dev` → `main`
- **Déploiement** : Quand la PR est mergée vers `main`
- **Avantages** : Validation manuelle avant production, historique des déploiements visible

## Configuration requise

Vous devez configurer les secrets suivants dans votre repository GitHub (Settings → Secrets and variables → Actions) :

### Secrets obligatoires
- `PROD_HOST` : Adresse IP ou nom de domaine du serveur de production
- `PROD_USERNAME` : Nom d'utilisateur SSH pour la connexion au serveur
- `PROD_SSH_KEY` : Clé privée SSH pour l'authentification
- `PROD_PORT` : Port SSH (généralement 22)
- `PROD_PROJECT_PATH` : Chemin absolu vers le projet sur le serveur (ex: `/root/cnot/cnot`)

### Configuration SSH
1. Générer une paire de clés SSH sur votre serveur de production :
   ```bash
   ssh-keygen -t rsa -b 4096 -C "github-actions@yourdomain.com"
   ```

2. Ajouter la clé publique au fichier `~/.ssh/authorized_keys` de l'utilisateur cible

3. Copier la clé privée dans le secret `PROD_SSH_KEY`

## Utilisation

Le workflow fonctionne en deux étapes :

1. **Push sur `dev`** → Création automatique d'une PR vers `main`
2. **Merge de la PR** → Déploiement automatique en production

### Flux de travail
1. Développez sur la branche `dev`
2. Poussez vos changements : `git push origin dev`
3. Une PR sera automatiquement créée vers `main`
4. Reviewez la PR et mergez-la quand vous êtes prêt à déployer
5. Le déploiement se lance automatiquement

## Structure des fichiers

```
.github/
└── workflows/
    ├── auto-pr-production.yml         # Création auto de PR dev → main
    ├── production-deployment.yml      # Déploiement après merge sur main
    └── README.md                      # Ce fichier
```

## Personnalisation

Vous pouvez modifier les workflows selon vos besoins :
- Ajouter des tests automatisés avant le déploiement
- Modifier les commandes Docker selon votre configuration
- Ajouter des notifications (Slack, Discord, email)
- Configurer des environnements de staging

## Dépannage

### Erreur de connexion SSH
- Vérifiez que la clé SSH est correctement configurée
- Assurez-vous que l'utilisateur a les permissions sudo si nécessaire

### Erreur Docker
- Vérifiez que Docker et Docker Compose sont installés sur le serveur
- Assurez-vous que l'utilisateur est dans le groupe `docker`

### Erreur Git
- Vérifiez que le repository est cloné sur le serveur de production
- Assurez-vous que la branche `main` existe et est trackée
