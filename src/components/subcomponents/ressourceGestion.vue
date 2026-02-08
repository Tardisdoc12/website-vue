<template>
    <!-- Le reste -->
    <DepliantWindow
        :key="key"
        v-for="(cats, key) in filteredSubcats"
        :title="cats.categorie_title"
        :backgroundColorOpen="'#2d5c7f'"
        :writenColorOpen="'#FFFFFF'"
        :border-color="'#2d5c7f'"
        :border-color-open="'#2d5c7f'"
        :borderWindowColor="'#2d5c7f'"
        :width="'95%'"
    >
        <DepliantWindow
            v-for="(subcats, subkey) in cats.subcats"
            :title="subcats.subcat_title"
            :key="subkey"
            :width="'100%'"
            :backgroundColor="'#d4e3ed'"
            :borderColorOpen="'#2d5c7f'"
            :borderColor="'#2d5c7f'"
            :backgroundColorOpen="'#d4e3ed'"
            :writenColor="'#245473'"
            :writenColorOpen="'#245473'"
            :showBorder="false"
            :borderRadius="'0px'"
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
                            v-if="isBureau"
                            @click="UpdateFile(file, subkey, key)"
                            class="appearance-none"
                            :style="{
                                display: 'inline-block',
                                color: 'white',
                                padding: '0.5rem 1rem',
                                borderRadius: '0.375rem',
                                backgroundColor: '#000000',
                            }"
                        >
                            <font-awesome-icon icon="fa-solid fa-pen-to-square"/>
                        </button>
                        <button
                            v-if="isBureau"
                            @click="DeleteFile(file)"
                            class="appearance-none"
                            :style="{
                                display: 'inline-block',
                                color: 'white',
                                padding: '0.5rem 1rem',
                                borderRadius: '0.375rem',
                                backgroundColor: '#df2727',
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

    <ModalFileUpdate
        v-if="isUpdateFile"
        :file="fileToUpdate"
        :sub-categorie-and-sources="subCategoriesAndSources"
         @cancelSignal="isUpdateFile = false"
         @updateSubCategoriesAndSources="UpdateHandler"
    />
</template>

<script>
import axios_sources from "../../javascript/axios_sources.js";
import DepliantWindow from './unitary_elements/depliantWindow.vue';
import ModalFileUpdate from './modal_update_file.vue';
import api_upload from '@/javascript/axios_upload'
import { toRaw } from "vue"

export default {

    props: {
        subCategoriesAndSources: {
            required: true,
            type: Object
        },
        isBureau: {
            required: true,
            type: Boolean
        }
    },

    data(){
        return {
            subCategoriesAndSourcesCopy : null,
            isUpdateFile: false,
            fileToUpdate: null
        }
    },

    mounted() {
        this.subCategoriesAndSourcesCopy = structuredClone(toRaw(this.subCategoriesAndSources));
    },

    computed: {
        filteredSubcats() {
            return Object.fromEntries(
                Object.entries(this.subCategoriesAndSources || {}).filter(
                    ([_, cat]) =>
                        cat?.categorie_title !== "Gestion" || this.isBureau
                )
            );
        }  
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
                const wpId = Number(file.id_wp)

                if (wpId > 0) {
                    await api_upload.delete_file(wpId)
                }
                
                if (response.data.success) {
                    // Supprimer le fichier de la structure locale
                    const subcat = this.subCategoriesAndSourcesCopy[file.id_categorie]?.subcats[file.id_subcategorie]

                    if (!subcat) return

                    subcat.files = subcat.files.filter(
                        f => f.source_id !== file.source_id
                    )
                    this.$emit("updateSubCategoriesAndSources", this.subCategoriesAndSourcesCopy)
                } else {
                    alert("Une erreur est survenue lors de la suppression du fichier.");
                }
            }
        },
        UpdateFile(file,subcat_id,categorie_id) {
            this.isUpdateFile = true
            this.fileToUpdate = file
            this.fileToUpdate.id_categorie = categorie_id
            this.fileToUpdate.id_subcategorie = subcat_id
        },
        DownloadUrl(file) {
            const link = document.createElement('a');
            link.href = file.path_file;
            link.download = file.path_file.split('/').pop();
            link.click();
        },
        UpdateHandler(updatedFile) {
            const subcat = this.subCategoriesAndSourcesCopy[updatedFile.id_categorie]?.subcats[updatedFile.id_subcategorie]
            if (!subcat) return

            const fileIndex = subcat.files.findIndex(f => f.source_id === updatedFile.source_id)

            if (fileIndex !== -1) {
                subcat.files[fileIndex] = updatedFile
                this.$emit("updateSubCategoriesAndSources", this.subCategoriesAndSourcesCopy)
            }
        },
    },

    components: {
        DepliantWindow,
        ModalFileUpdate
    }
}
</script>

<style>

</style>