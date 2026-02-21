<template>
    <ModalComponent
        title="Update file"
        @changeBool="Cancel"
    >
        <form @submit.prevent="UpdateFile" class="flex flex-col gap-4">
            <div style="margin-left: 20px; margin-right: 20px;margin-top: 10px; margin-bottom: 10px;">
                <!-- Choix du fichier -->
                <div>
                    <!-- Le type de fichier qu'on veut ajouter -->
                    <div class="flex flex-col gap-1">
                        <label>{{ "Fichier ou url" }}</label>
                        <select v-model="typeAdd">
                            <option disabled value="">Choisissez</option>
                            <option>{{ "Fichier" }}</option>
                            <option>{{ "Url" }}</option>
                        </select>
                    </div>

                    <div
                        v-if="typeAdd === 'Fichier'"
                        class="flex flex-col gap-1"
                        style="margin-top: 10px;"
                    >
                        <label>{{ "Ancien Fichier séléctionner" }}</label>
                        <button
                            @click="openUrl(file.path_file)"
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
                    </div>

                    <div
                        v-if="typeAdd === 'Fichier'"
                        class="flex flex-col gap-1"
                        style="margin-top: 10px;"
                    >
                        <label>{{ "Fichier à uploader (PDF seulement)" }}</label>
                        <div class="border border-gray-300 rounded-lg p-3 flex items-center justify-between">
                            <input
                                id="pdfFile"
                                type="file"
                                accept="application/pdf"
                                @change="handleFileUpload"
                            />
                        </div>
                    </div>

                    <div
                        v-if="typeAdd === 'Fichier'"
                        class="flex flex-col gap-1"
                        style="margin-top: 10px;"
                    >
                        <label>{{ "Url à ajouter (visualisation)" }}</label>
                        <input
                            type="url"
                            v-model="pdfUrl"
                            placeholder="https://exemple.com/mon-document.pdf"
                            class="border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-400 outline-none"
                        />
                    </div>

                    <div
                        v-if="typeAdd === 'Url'"
                        class="flex flex-col gap-1"
                        style="margin-top: 10px;"
                    >
                        <label>{{ "Url à ajouter" }}</label>
                        <input
                            type="url"
                            v-model="pdfUrl"
                            placeholder="https://exemple.com"
                            class="border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-400 outline-none"
                        />
                    </div>

                    <!-- Tag Name -->
                    <div
                        v-if="typeAdd !== 'Aucun' && typeAdd"
                        class="flex flex-col gap-1"
                        style="margin-top: 10px;"
                    >
                        <label>{{ "Nom à afficher pour le fichier" }}</label>
                        <input
                            v-model="tag"
                            type="text"
                            class="w-full border p-1 rounded"
                            required
                        />
                    </div>

                    <div class="flex items-center justify-center " style="margin-bottom:10px;margin-top: 15px;">
                        <button
                            :disabled="isDisable"
                            type="submit"
                            class="appearance-none button-base"
                            :style="{
                                '--btn-bg' : isDisable ? Couleurs.gris_pale : Couleurs.main_blue,
                                '--btn-hover-bg' : isDisable ? Couleurs.gris_pale : Couleurs.dark_blue,
                            }"
                        >
                            Mettre à jour le document
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </ModalComponent>
</template>

<script>
import ModalComponent from '@/subcomponents/unitary_elements/modalComponent.vue';
import apiUpload from '@/javascript/api/axios_upload';
import apiSource from '@/javascript/api/axios_sources';
import { Couleurs } from '@/javascript/constants/colors'

export default {
    props: {
        file: {
            type: Object,
            required: true
        },
        subCategorieAndSources: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            Couleurs,
            isOpen: true,
            fileCopy : {...this.file},
            typeAdd: this.file.path_file !== '' ? 'Fichier' : 'Url',
            pdfUrl: this.file.url_file,
            pdfFile: null,
            tag : this.file.tag
        }
    },

    computed:{
        isDisable() {
            if(this.typeAdd === "") {
                return true
            }
            if(!this.pdfFile && !this.pdfUrl) {
                return true
            }
            if(!this.tag) {
                return true
            }
            return false
        },
    },

    methods: {
        openUrl(file_path) {
            window.open(file_path,"_blank");
            
        },
        handleFileUpload(event) {
            this.pdfFile = event.target.files[0]
        },
        Cancel() {
            this.$emit('cancelSignal', !this.isOpen)
        },
        async UpdateFile() {
            let pathPdf = this.fileCopy.path_file
            let pdfIDWP = this.fileCopy.id_wp
            if(!this.pdfFile && this.typeAdd === "Url" && this.fileCopy.path_file) {
                pathPdf = ""
                pdfIDWP = 0
                const response = await apiUpload.delete_file(this.fileCopy.id_wp)
                if(response.status === 200) {
                    console.log("Ancien fichier supprimé avec succès")
                } else {
                    console.error("Erreur lors de la suppression de l'ancien fichier")
                }
            }
            if(this.pdfFile) {
                if(this.fileCopy.path_file && Number(this.fileCopy.id_wp) !== 0) {
                    const response_2 = await apiUpload.delete_file(this.fileCopy.id_wp)
                    if(response_2.status === 200) {
                        console.log("Ancien fichier supprimé avec succès")
                    } else {
                        console.error("Erreur lors de la suppression de l'ancien fichier")
                    }
                }
                const formData = new FormData();
                formData.append('file', this.pdfFile);
                const uploadResponse = await apiUpload.upload_file(formData);
                if (uploadResponse.data.source_url) {
                    pathPdf = uploadResponse.data.source_url
                    pdfIDWP = uploadResponse.data.id
                }
            }
            const UpdateFile = {
                ...this.fileCopy,
                "path_file": pathPdf,
                "url_file": this.pdfUrl,
                "tag": this.tag,
                "id_wp": pdfIDWP,
            }
            const finalAnswer = await apiSource.update_source(UpdateFile)
            if(finalAnswer.status === 200) {
                this.$emit('updateSubCategoriesAndSources', UpdateFile)
            } else {
                console.error("Erreur lors de la mise à jour du fichier")
            }
            this.Cancel()
        }
    },
    components: {
        ModalComponent
    }
}

</script>

<style>
</style>