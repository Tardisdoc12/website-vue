#!/bin/bash
set -e  # stoppe en cas d'erreur

# Dossier cible pour ton plugin
OUTDIR="./vue-app"
mkdir -p "$OUTDIR"

# Liste des entrées à builder
ENTRIES=("login" "events" "calendar" "connexion" "account" "form_adhesion")

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

# Copie du fichier principal PHP du plugin
cp vue-app.php "$OUTDIR"/

echo "✅ Build terminé ! Les fichiers sont dans $OUTDIR/"