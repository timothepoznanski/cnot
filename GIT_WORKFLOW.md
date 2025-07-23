# Guide pour éviter les conflits Git

## 🎯 Objectif
Ce guide t'aide à éviter les conflits lors de la création de Pull Requests (PR) entre `dev` et `main`.

## 🔍 Pourquoi ces conflits apparaissent-ils ?

Les conflits surviennent quand :
1. ✅ Tu travailles sur la branche `dev`
2. 🔄 Pendant ce temps, la branche `main` reçoit de nouveaux commits (merges d'autres PRs)
3. 📝 Tu modifies les mêmes fichiers que ceux modifiés dans `main`
4. ⚠️ Quand tu crées une PR, Git ne peut pas automatiquement merger les changements

## 🛠️ Solution recommandée

### Option 1: Script automatique (recommandé)
Utilise le script `sync-with-main.sh` avant chaque PR :

```bash
./sync-with-main.sh
```

### Option 2: Commandes manuelles
Si tu préfères faire manuellement :

```bash
# 1. Assure-toi d'être sur dev
git checkout dev

# 2. Récupère les dernières modifications
git fetch origin

# 3. Merge main dans dev
git merge origin/main

# 4. Si il y a des conflits, résous-les
# 5. Puis pousse
git push origin dev
```

## 📅 Quand synchroniser ?

### ✅ Fais-le systématiquement :
- **Avant** de créer une PR
- **Après** que des PRs ont été mergées dans `main`
- **Au début** de chaque session de travail

### ⚠️ Attention :
- Ne synchronise jamais avec des modifications non commitées
- Toujours commiter tes changements avant la synchronisation

## 🔧 Résolution de conflits

Quand des conflits apparaissent :

1. **Identifie les fichiers en conflit** :
   ```bash
   git status
   ```

2. **Ouvre chaque fichier** et cherche les marqueurs :
   ```
   <<<<<<< HEAD
   Ton code (dev)
   =======
   Code de main
   >>>>>>> origin/main
   ```

3. **Décide quoi garder** :
   - Généralement, garde les améliorations de `dev`
   - Assure-toi que le code final fonctionne

4. **Supprime les marqueurs** et garde le code final

5. **Marque comme résolu** :
   ```bash
   git add fichier-résolu.php
   ```

6. **Finalise** :
   ```bash
   git commit
   git push origin dev
   ```

## 🎯 Bonnes pratiques pour l'équipe

### Pour éviter les conflits futurs :
1. **Communiquez** sur les fichiers que vous modifiez
2. **Synchronisez régulièrement** avec main
3. **Créez des PRs plus petites** et plus fréquentes
4. **Mergez rapidement** les PRs approuvées

### Workflow recommandé :
```
dev (votre travail) ← sync régulière ← main (production)
     ↓ PR
   main (après review)
```

## 🚀 Automatisation

Pour l'avenir, considérez :
- **GitHub Actions** pour auto-sync
- **Branch protection rules** sur main
- **Pre-commit hooks** pour vérifications

## ❓ En cas de problème

Si tu es bloqué :
1. Sauvegarde ton travail : `git stash`
2. Retourne à un état propre : `git reset --hard origin/dev`
3. Redemande de l'aide avec le contexte spécifique

## 📞 Contacts
- Documentation Git : https://git-scm.com/doc
- Pour les urgences : crée une issue GitHub
