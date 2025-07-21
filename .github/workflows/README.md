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
- `PROD_SSH_PASSPHRASE` : Passphrase de la clé SSH (si votre clé en a une)
- `PROD_PORT` : Port SSH (généralement 22)
- `PROD_PROJECT_PATH` : Chemin absolu vers le projet sur le serveur (ex: `/root/cnot/cnot`)
- `PAT_TOKEN` : Personal Access Token GitHub pour créer les Pull Requests

### Configuration SSH
1. Générer une paire de clés SSH sur votre serveur de production :
   ```bash
   ssh-keygen -t rsa -b 4096 -C "github-actions@yourdomain.com"
   ```

2. Ajouter la clé publique au fichier `~/.ssh/authorized_keys` de l'utilisateur cible

3. Copier la clé privée dans le secret `PROD_SSH_KEY`

4. **Si votre clé a une passphrase** : Ajoutez la passphrase dans le secret `PROD_SSH_PASSPHRASE`

### Configuration GitHub Personal Access Token
1. Allez sur GitHub → Settings → Developer settings → Personal access tokens → Tokens (classic)
2. Cliquez sur "Generate new token (classic)"
3. Donnez un nom au token (ex: "CnoT Auto Deploy")
4. Sélectionnez les permissions suivantes :
   - ✅ `repo` (Full control of private repositories)
   - ✅ `workflow` (Update GitHub Action workflows)
5. Cliquez sur "Generate token"
6. Copiez le token et ajoutez-le dans le secret `PAT_TOKEN`

### Option alternative : Clé sans passphrase
Si vous préférez, vous pouvez créer une clé SSH dédiée sans passphrase pour GitHub Actions :
```bash
ssh-keygen -t rsa -b 4096 -f ~/.ssh/github_actions_key -N ""
```
Dans ce cas, vous n'avez pas besoin du secret `PROD_SSH_PASSPHRASE`.

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

### Erreur "invalid header field value for Authorization"
Cette erreur indique un problème avec le Personal Access Token :
- ✅ Vérifiez que le secret `PAT_TOKEN` est bien configuré dans votre repository
- ✅ Assurez-vous que le token n'a pas expiré
- ✅ Vérifiez que le token a les permissions `repo` et `workflow`
- ✅ Le token ne doit pas contenir d'espaces ou de caractères spéciaux en début/fin

### Erreur de connexion SSH
- Vérifiez que la clé SSH est correctement configurée
- Assurez-vous que l'utilisateur a les permissions sudo si nécessaire

### Erreur Docker
- Vérifiez que Docker et Docker Compose sont installés sur le serveur
- Assurez-vous que l'utilisateur est dans le groupe `docker`

### Erreur Git
- Vérifiez que le repository est cloné sur le serveur de production
- Assurez-vous que la branche `main` existe et est trackée
