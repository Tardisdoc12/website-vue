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
            <div :key="file.source_id" v-for="file in getSortedFiles(subcats.files)">
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
                    <div class="flex items-center justify-between gap-2">
                        <button
                            @click="DeleteFile(file)"
                            class="appearance-none"
                            :style="{
                                display: 'inline-block',
                                color: 'white',
                                padding: '0.5rem 1rem',
                                borderRadius: '0.375rem',
                                backgroundColor: '#FF0000',
                            }"
                        >
                            <font-awesome-icon icon="fa-solid fa-trash"/>
                        </button>
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
                            }"
                        >
                            <font-awesome-icon icon="fa-solid fa-download"/>
                        </button>
                    </div>
                </div>
            </div>
        </DepliantWindow>
    </DepliantWindow>
</template>

<script>
import axios_sources from "../../javascript/axios_sources.js";
import DepliantWindow from './unitary_elements/depliantWindow.vue';
import api_upload from '@/javascript/axios_upload'

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
        getSortedFiles(files) {
            if (!files) return []

            return [...files].sort((a, b) =>
            a.tag.localeCompare(b.tag, 'fr', {
                numeric: true,
                sensitivity: 'base'
            })
            )
        },
        openUrl(url, file_path) {
            if (url!== "") {
                window.open(url, "_blank");
            }
            else {
                window.open(file_path,"_blank");
            }
            
        },
        async DeleteFile(file) {
            if (confirm("Êtes-vous sûr de vouloir supprimer ce fichier ?")) {
                const response = await axios_sources.delete_source(file.source_id);
                if (file.id_wp) {
                    const res = await api_upload.delete_file(file.id_wp);
                }
                if (response.data.success) {
                    // Supprimer le fichier de la structure locale
                    for (const catKey in this.structuresRessources) {
                        const cat = this.structuresRessources[catKey];
                        for (const subcatKey in cat.subcats) {
                            const subcat = cat.subcats[subcatKey];
                            const fileIndex = subcat.files.findIndex(f => f.source_id === file.source_id);
                            if (fileIndex !== -1) {               
                                subcat.files.splice(fileIndex, 1);
                                return; // Sortir une fois que le fichier est trouvé et supprimé
                            }
                        }
                    }
                } else {
                    alert("Une erreur est survenue lors de la suppression du fichier.");
                }
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