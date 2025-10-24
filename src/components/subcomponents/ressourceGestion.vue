<template>
    <!-- Feuille de Suivi -->
    <DepliantWindow
        :title="titleFeuille"
    >
    </DepliantWindow>
    
    <!-- Fiches et Exos -->
    <DepliantWindow
        :title="titleFicheExo"
    >
        <DepliantWindow
            v-for="label in categoriesCopy"
            :title="label"
            :key="label"
            :background-color="'#4897ff'"
            :background-color-open="'#4897ff'"
        >
        </DepliantWindow>
    </DepliantWindow>
    
    <!-- Parcours d'entrainement -->
    <DepliantWindow
        :title="titleParcours"
    >
    </DepliantWindow>
    
    <!-- Liens Utiles -->
    <DepliantWindow
        :title="titleLink"
    >
    </DepliantWindow>
</template>

<script>
import DepliantWindow from './unitary_elements/depliantWindow.vue';

export default {

    props: {
        categories: {
            required: true,
            type: Array
        }
    },

    data(){
        return {
            titleFeuille: "Feuille de Suivi",
            titleFicheExo: "Fiches et Exercices",
            titleParcours: "Parcours d'entrainement",
            titleLink: "Liens Utiles",
            catégories: [
                ...this.categories
            ],
        }
    },

    computed: {
        categoriesCopy() {
            return [
                ...this.categories
            ]
        }
    },

    components: {
        DepliantWindow
    }
}
</script>

<style>

</style>