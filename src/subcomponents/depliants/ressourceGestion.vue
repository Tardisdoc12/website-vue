<template>
    <!-- Le reste -->
    <DepliantWindow
        :key="key"
        v-for="(cats, key) in filteredSubcats"
        :title="cats.categorie_title"
        :backgroundColorOpen="Couleurs.dark_blue"
        :writenColorOpen="Couleurs.white"
        :border-color="Couleurs.dark_blue"
        :border-color-open="Couleurs.dark_blue"
        :borderWindowColor="Couleurs.dark_blue"
        :width="'95%'"
    >
        <DepliantWindow
            v-for="(subcats, subkey) in cats.subcats"
            :title="subcats.subcat_title"
            :key="subkey"
            :width="'100%'"
            :backgroundColor="Couleurs.cyan"
            :borderColorOpen="Couleurs.dark_blue"
            :borderColor="Couleurs.dark_blue"
            :backgroundColorOpen="Couleurs.cyan"
            :writenColor="Couleurs.main_blue"
            :writenColorOpen="Couleurs.main_blue"
            :showBorder="false"
            :borderRadius="'0px'"
        >
            <div :key="file.source_id" v-for="file in getSortedFiles(subcats.files)">
                <FileComponent
                    :file="file"
                    :can-be-favoris="Number(key) === 0"
                    :can-be-updated="isBureau"
                    @update-sub-categories-and-sources="DeleteFile"
                    @updateFile="(file)=>{UpdateFile(file, subkey, key)}"
                />
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
import DepliantWindow from '@/subcomponents/unitary_elements/depliantWindow.vue';
import ModalFileUpdate from '@/subcomponents/modals/modal_update_file.vue';
import FileComponent from "../unitary_elements/file_component.vue";
import { Couleurs } from "@/javascript/constants/colors";
import { toRaw } from "vue"

export default {
    emits:[
        "updateSubCategoriesAndSources"
    ],
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
            Couleurs,
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

        async DeleteFile(file) {
            // Supprimer le fichier de la structure locale
            const subcat = this.subCategoriesAndSourcesCopy[file.id_categorie]?.subcats[file.id_subcategorie]

            if (!subcat) return

            subcat.files = subcat.files.filter(
                f => f.source_id !== file.source_id
            )
            this.$emit("updateSubCategoriesAndSources", this.subCategoriesAndSourcesCopy)
        },

        UpdateFile(file,subcat_id,categorie_id) {
            this.isUpdateFile = true
            this.fileToUpdate = file
            this.fileToUpdate.id_categorie = categorie_id
            this.fileToUpdate.id_subcategorie = subcat_id
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
        ModalFileUpdate,
        FileComponent
    }
}
</script>

<style>

</style>