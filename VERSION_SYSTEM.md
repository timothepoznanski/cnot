# Système de versioning automatique

## Fonctionnement

Quand un push est effectué sur la branche `main`, GitHub Actions :

1. **Génère un timestamp** au format `YYYYMMDDHHMM` (ex: `202501241530` pour le 24 janvier 2025 à 15h30)
2. **Tag l'image Docker** avec ce timestamp au lieu du SHA Git
3. **Déploie l'application** avec la nouvelle version
4. **Crée un fichier `version.txt`** contenant le timestamp sur le serveur

## Affichage dans l'interface

- Le texte "Last deployment" a été remplacé par "**Version**"
- Le timestamp est formaté de manière lisible (ex: `24/01/2025 15:30`)
- En développement, affiche "Development" au lieu d'un timestamp

## Fichiers modifiés

- `.github/workflows/production-deployment.yml` : Génération du timestamp et déploiement
- `src/functions.php` : Fonction `getDeploymentVersion()` pour lire et formater la version
- `src/index.php` : Affichage de la version au lieu de "last deployment"
- `src/version.txt` : Fichier contenant la version actuelle (créé automatiquement en production)

## Format du timestamp

- **Format brut** : `YYYYMMDDHHMM` (ex: `202501241530`)
- **Format affiché** : `DD/MM/YYYY HH:MM` (ex: `24/01/2025 15:30`)
- **Développement** : `Development`

## Exemples

| Timestamp brut | Affiché |
|---|---|
| `202501241530` | `24/01/2025 15:30` |
| `202512311159` | `31/12/2025 11:59` |
| `dev` | `Development` |
