#!/bin/bash
set -e  # stoppe en cas d'erreur

# Dossier cible pour ton plugin
OUTDIR="./vue-app"

# Liste des entrées à builder
ENTRIES=("login" "events" "calendar" "connexion" "account" "form_adhesion" "test")

# Boucle sur chaque entrée
for entry in "${ENTRIES[@]}"; do
  echo "⚡ Build $entry..."
  ENTRY=$entry npm run build:$entry

  echo "📦 Copie des fichiers $entry..."
  cp dist/$entry.js "$OUTDIR"/
  if [ -f "dist/$entry.css" ]; then
    cp dist/$entry.css "$OUTDIR"/
  fi
done

echo "✅ Build terminé ! Les fichiers sont dans $OUTDIR/"