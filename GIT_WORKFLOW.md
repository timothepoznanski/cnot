# Guide pour éviter les conflits Git

## 🎯 Objectif
Ce guide t'aide à éviter les conflits lors de la création de Pull Requests (PR) entre `dev` et `main`.

## 🔍 Pourquoi ces conflits apparaissent-ils ?

Les conflits surviennent quand :
1. ✅ Tu travailles sur la branche `dev`
2. 🔄 Tu crées une PR et elle est mergée dans `main`
3. 🤖 **Le workflow de déploiement automatique modifie `main`** lors du processus
4. 📝 GitHub peut consolider/reformater tes commits différemment lors du merge
5. ⚠️ Quand tu crées ta prochaine PR, Git voit une divergence même si c'est le même contenu

**Note importante:** Même si tu es seul sur le repo, les **workflows automatiques** (GitHub Actions, déploiements) peuvent modifier `main` et créer ces divergences.

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
- **Après** qu'une PR a été mergée dans `main` (même la tienne!)
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
   Code de main (souvent identique mais reformaté)
   >>>>>>> origin/main
   ```

3. **Décide quoi garder** :
   - **Généralement, garde les améliorations de `dev`**
   - Les conflits sont souvent dus à des reformatages automatiques
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

## 🤖 Pourquoi ça arrive avec les workflows automatiques ?

Ton setup a des **GitHub Actions** qui :
1. Créent automatiquement des PRs de `dev` vers `main`
2. Déploient automatiquement quand `main` change
3. **Peuvent modifier légèrement les commits** lors du processus

**Exemple :** Le commit sur `main` peut grouper plusieurs de tes commits `dev` en un seul, créant une divergence.

## 🎯 Bonnes pratiques pour ton workflow automatique

### Pour éviter les conflits futurs :
1. **Synchronise systématiquement** après chaque merge
2. **Utilise le script** avant chaque nouveau développement
3. **Ne modifie jamais `main` directement**

### Workflow recommandé avec automation :
```
dev (ton travail) → PR automatique → main (après merge) → déploiement auto
     ↑                                  ↓
     ← sync régulière avec script ←←←←←←←
```

## 🚀 Optimisation future

Pour améliorer ton workflow :
- **Squash merge** dans GitHub pour des commits plus propres
- **Rebase** automatique dans les workflows
- **Protection branch** sur main avec review obligatoire

## ❓ En cas de problème

Si tu es bloqué :
1. Sauvegarde ton travail : `git stash`
2. Retourne à un état propre : `git reset --hard origin/dev`
3. Redemande de l'aide avec le contexte spécifique

## 📞 Contacts
- Documentation Git : https://git-scm.com/doc
- Pour les urgences : crée une issue GitHub
