#!/bin/bash

# Script pour synchroniser la branche dev avec main et éviter les conflits
# Usage: ./sync-with-main.sh

set -e  # Arrêter le script en cas d'erreur

echo "🔄 Synchronisation de la branche dev avec main..."

# Vérifier qu'on est sur la branche dev
current_branch=$(git branch --show-current)
if [ "$current_branch" != "dev" ]; then
    echo "❌ Erreur: Vous devez être sur la branche dev pour exécuter ce script"
    echo "   Branche actuelle: $current_branch"
    echo "   Exécutez: git checkout dev"
    exit 1
fi

# Vérifier qu'il n'y a pas de modifications non commitées
if ! git diff --quiet || ! git diff --cached --quiet; then
    echo "❌ Erreur: Vous avez des modifications non commitées"
    echo "   Commitez ou stashez vos modifications avant de continuer"
    git status --short
    exit 1
fi

# Récupérer les dernières modifications
echo "📥 Récupération des dernières modifications..."
git fetch origin

# Vérifier s'il y a des nouveaux commits sur main
main_commits=$(git rev-list --count dev..origin/main)
if [ "$main_commits" -eq 0 ]; then
    echo "✅ La branche dev est déjà à jour avec main"
    exit 0
fi

echo "📊 $main_commits nouveaux commits trouvés sur main"

# Merger main dans dev
echo "🔄 Merge de main dans dev..."
if git merge origin/main --no-edit; then
    echo "✅ Merge réussi sans conflits"
else
    echo "⚠️  Conflits détectés! Résolution nécessaire..."
    echo ""
    echo "📋 Fichiers en conflit:"
    git status --porcelain | grep "^UU" | sed 's/^UU /  - /'
    echo ""
    echo "🛠️  Étapes suivantes:"
    echo "   1. Résolvez les conflits dans les fichiers listés ci-dessus"
    echo "   2. Utilisez 'git add <fichier>' pour marquer chaque conflit comme résolu"
    echo "   3. Exécutez 'git commit' pour finaliser le merge"
    echo "   4. Puis 'git push origin dev' pour pousser les changements"
    exit 1
fi

# Pousser les changements
echo "📤 Push vers origin/dev..."
git push origin dev

echo "🎉 Synchronisation terminée avec succès!"
echo ""
echo "📋 Prochaines étapes recommandées:"
echo "   - Créez votre PR depuis GitHub"
echo "   - La PR ne devrait plus avoir de conflits"
