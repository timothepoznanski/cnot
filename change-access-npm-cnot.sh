#!/bin/bash

# Script pour changer l'Access List d'un proxy host NPM
# Usage: 
#   ./change-access-npm-cnot.sh        -> Mode public (pas d'access list)
#   ./change-access-npm-cnot.sh admin  -> Mode admin (access list requise)

NPM_HOST="http://localhost:81"
PROXY_ID=14
LOGIN="tim.poznanski@gmail.com"

# Gestion des paramètres
if [ "$1" == "admin" ]; then
    NEW_ACL_ID=1  # Access List "admin"
    echo "🔒 Mode: Access List Admin (ID: 1)"
else
    NEW_ACL_ID=0  # Public (pas d'access list)
    echo "🌐 Mode: Public (pas d'access list)"
fi

# Demander le mot de passe de façon sécurisée (ne s'affiche pas)
echo -n "Mot de passe NPM: "
read -s PASSWORD
echo

# Auth
TOKEN=$(curl -s -X POST "$NPM_HOST/api/tokens" \
  -H "Content-Type: application/json" \
  -d "{\"identity\": \"$LOGIN\", \"secret\": \"$PASSWORD\"}" | jq -r .token)

# Vérifier l'authentification
if [ "$TOKEN" == "null" ] || [ -z "$TOKEN" ]; then
    echo "❌ Erreur d'authentification"
    exit 1
fi

echo "✅ Authentification réussie"

# Récupération config existante
EXISTING=$(curl -s -X GET "$NPM_HOST/api/nginx/proxy-hosts/$PROXY_ID" \
  -H "Authorization: Bearer $TOKEN")

echo "=== DONNÉES RÉCUPÉRÉES (EXISTING) ==="
echo "$EXISTING" | jq .

# Modification du access_list_id
UPDATED=$(echo "$EXISTING" | jq ".access_list_id = $NEW_ACL_ID")

echo "=== DONNÉES APRÈS MODIFICATION (UPDATED) ==="
echo "$UPDATED" | jq .

# Nettoyage du JSON - suppression des propriétés non autorisées pour PUT
CLEANED=$(echo "$UPDATED" | jq 'del(.id, .created_on, .modified_on, .owner_user_id, .is_deleted)')

echo "=== DONNÉES NETTOYÉES (CLEANED) ==="
echo "$CLEANED" | jq .

# Envoi du PUT avec la config nettoyée
RESULT=$(curl -s -X PUT "$NPM_HOST/api/nginx/proxy-hosts/$PROXY_ID" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d "$CLEANED")

echo "=== RÉSULTAT DE L'API ==="
echo "$RESULT" | jq .

