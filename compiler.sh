#!/bin/bash
set -e

WORDPRESS_CODE_DIR="wordpress_code"
PLUGIN_DIR="vue-app"
BUILD_DIR="./build"
FINAL_DIR="$BUILD_DIR/$PLUGIN_DIR"

echo "📁 Nettoyage ancien build..."
rm -rf "$BUILD_DIR"
mkdir -p "$FINAL_DIR"

echo "⚡ Build Vite..."
npm run build

echo "📦 Copie des fichiers WordPress..."
cp -r "$WORDPRESS_CODE_DIR"/* "$FINAL_DIR/"

echo "📦 Copie du dossier dist..."
cp -r "$BUILD_DIR"/dist/ "$FINAL_DIR/"

echo "🗜 Création du ZIP..."
cd "$BUILD_DIR"
zip -r "$PLUGIN_DIR.zip" "$PLUGIN_DIR"
cd ..

echo "✅ Plugin prêt : $BUILD_DIR/$PLUGIN_DIR.zip"
