<template>
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
            :width="'100%'"
            :background-color="'#d4e3ed'"
            :background-color-open="'#d4e3ed'"
            :writenColor="'#245473'"
            :writenColorOpen="'#245473'"
        >
            <div :key="file.source_id" v-for="file in subcats.files">
                <div 
                    style="padding: 5px;"
                    class="flex items-center justify-between"
                >
                    <label
                        class="flex-1 truncate mr-2" :title="file.path_file !== '' ? file.path_file : file.url_file"
                    >
                        <font-awesome-icon style="margin-right:5px;" icon="fa-solid fa-file-lines"/>
                        {{ (file.tag !== "") ? file.tag : file.url_file }}
                    </label>
                    <div>
                        <button
                            @click="openUrl(file.url_file, file.path_file)"
                            class="appearance-none"
                            :style="{
                                display: 'inline-block',
                                color: 'white',
                                padding: '0.5rem 1rem',
                                borderRadius: '0.375rem',
                                backgroundColor: '#000000',
                            }"
                        >
                            <font-awesome-icon icon="fa-solid fa-eye"/>
                        </button>
                        <button
                            v-if="file.path_file !== ''"
                            @click="DownloadUrl(file)"
                            class="appearance-none"
                            :style="{
                                display: 'inline-block',
                                color: 'white',
                                padding: '0.5rem 1rem',
                                borderRadius: '0.375rem',
                                backgroundColor: '#000000',
                                marginLeft: '5px',
                            }"
                        >
                            <font-awesome-icon icon="fa-solid fa-arrow-up-from-bracket"/>
                        </button>
                    </div>
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
        isSuivi(_label) {
            return false
        },

        openUrl(url, file_path) {
            if (url!== "") {
                window.open(url, "_blank");
            }
            else {
                window.open(file_path,"_blank");
            }
            
        },
        DownloadUrl(file) {
            const link = document.createElement('a');
            link.href = file.path_file;
            link.download = file.path_file.split('/').pop();
            link.click();
        }
    },

    components: {
        DepliantWindow
    }
}
</script>

<style>

</style>