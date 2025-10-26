<template>

    <!-- Feuille de Suivi -->
    <DepliantWindow
        :title="titleFeuille"
    >
    </DepliantWindow>

    <!-- Le reste -->
    <DepliantWindow
        :key="key"
        v-for="(cats, key) in StructuresCopy"
        :title="cats.categorie_title"
    >
        <DepliantWindow
            v-for="(subcats, subkey) in cats.subcats"
            :title="subcats.subcat_title"
            :key="subkey"
            :background-color="'#4897ff'"
            :background-color-open="'#4897ff'"
        >
            <div :key="file.source_id" v-for="file in subcats.files">
                <div 
                    style="padding: 5px;"
                    class="flex items-center justify-between"
                >
                    <template v-if="file.url_file && file.url_file.trim() !== ''">
                        <label
                            class="flex-1 truncate mr-2" :title="file.url_file"
                        >
                            {{ (file.tag !== "") ? file.tag : file.url_file }}
                        </label>
                        <button
                            @click="openUrl(file.url_file)"
                            class="appearance-none"
                            :style="{
                                display: inline-block,
                                color: white,
                                padding: '0.5rem 1rem',
                                borderRadius: '0.375rem',
                                backgroundColor: '#000000',
                            }"
                        >
                            <font-awesome-icon icon="fa-solid fa-eye"/>
                        </button>
                    </template>
                    <template v-else>
                        <label>
                            {{ file?.tag ?? file.path_file }}
                        </label>
                    </template>
                </div>
            </div>
        </DepliantWindow>
    </DepliantWindow>
</template>

<script>
import DepliantWindow from './unitary_elements/depliantWindow.vue';

export default {

    props: {
        subCategoriesAndSources: {
            required: true,
            type: Object
        }
    },

    data(){
        return {
            titleFeuille: "Feuille de Suivi",
            titleFicheExo: "Fiches et Exercices",
            titleParcours: "Parcours d'entrainement",
            titleLink: "Liens Utiles",
        }
    },

    computed: {
        StructuresCopy() {
            return {
                ...this.subCategoriesAndSources
            }
        },
    },

    methods:{
        isSuivi(label) {
            if(label === this.titleFeuille) {
                return true
            }
            return false
        },

        openUrl(url) {
            window.open(url, "_blank");
        },
    },

    components: {
        DepliantWindow
    }
}
</script>

<style>

</style>