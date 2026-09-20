# MPS_TOOLS

## Introduction

Ce plugin a pour but de permettre d'avoir des outils simple pour tout site d'association (de moto). Il permet la gestion d'événements et d'inscription ainsi que les paiements avec helloAsso.

## Libraries utilisés:

J'utilise la librairie de Kallookoo qui est :
https://github.com/kallookoo/wp-color-picker-alpha

Elle permet d'avoir accés aux canaux alpha pour les couleurs de manière plus aisées dans les paramètres.

## Documentation

Après de l'installation du plugin, il est important de lire la documentation dans la page de paramètre. Cela expliquera les fonctionnalités de cette dernière

## Building de l'application

Pour build ce plugin (sous linux) voici les étapes:

```sh
git clone https://github.com/Tardisdoc12/website-vue.git
cd website-vue
npm install
```

Ensuite une fois que cela est fait, vous pouvez exécuter le script de création du plugin tel que:

```sh
chmod +x compiler.sh
./compiler.sh
```

Vous aurez ensuite le plugin prêt à l'installation dans le chemin:

```
build/vue-app.zip
```