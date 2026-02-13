<template>
    <ModalComponent
        title="Ajouter un document"
        :isCancel="isOpen"
        @changeBool="Cancel"
    >
        <form @submit.prevent="handleSubmit" class="space-y-4">
            <div style="margin-left: 20px; margin-right: 20px;margin-top: 10px; margin-bottom: 10px;">
            
                <!-- Choix de la catégorie -->
                <div class="flex flex-col gap-1">
                    <label class="block font-medium">
                        Nom de la catégorie
                        <span style="color: red;">*</span>
                    </label>
                    <select v-model="categorieSelected">
                        <option disabled value="">Choisissez</option>
                        <option :key="key" v-for="(categorie, key) in subCategoriesAndSources" :value="key">{{ categorie.categorie_title }}</option>
                    </select>
                </div>

                <!-- Choix de la sous-catégorie -->
                <div class="flex flex-col gap-1" v-if="categorieSelected">
                    <label class="block font-medium">
                        Sélection de la sous-catégorie
                    </label>
                    <select 
                        
                        v-model="sousCategorieSelected"
                    >
                        <option disabled value="">Choisissez</option>
                        <option
                            :key="keySub"
                            v-for="(sousCategorie, keySub) in subCategoriesAndSources[categorieSelected].subcats"
                            :value="sousCategorie.subcat_title"
                        >
                            {{ sousCategorie.subcat_title }}
                        </option>
                    </select>
                </div>

                <!-- Choix du fichier -->
                <div v-if="sousCategorieSelected">
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
                </div>

                <!-- Boutons de validation -->
                <div class="flex items-center justify-center " style="margin-bottom:10px;margin-top: 15px;">
                    <button
                        :disabled="isDisable"
                        type="submit"
                        class="px-4 py-2 rounded-lg font-medium text-white transition"
                        :class="isDisabled ? 'bg-gray-400 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700'"
                    >
                        Ajouter le document
                    </button>
                </div>

            </div>
        </form>
    </ModalComponent>
</template>

<script>
import ModalComponent from '@/subcomponents/unitary_elements/modalComponent.vue';
import apiUpload from '@/javascript/api/axios_upload'
import apiSource from '@/javascript/api/axios_sources';

export default{

    props:{
        subCategoriesAndSources: {
            type: Object,
            required: true
        }
    },
    
    data(){
        return {
            isOpen: true,
            categories: [
                "Fiches et Exos",
                "Parcours d'entrainement",
                "Liens Utiles"
            ],
            categorieSelected: "",
            sousCategorieSelected: "",
            typeAdd: "",
            pdfFile: null,
            pdfUrl: null,
            tag: null,
        }
    },

    computed:{
        isDisable() {
            if(this.typeAdd === "") {
                return true
            }
            if( (!this.pdfFile && !this.pdfUrl) && this.typeAdd !== "Aucun") {
                return true
            }
            if(!this.tag) {
                return true
            }
            return false
        },
    },

    methods:{
        Cancel() {
            this.$emit('cancelSignal', !this.isOpen)
        },

        handleFileUpload(event) {
            this.pdfFile = event.target.files[0]
            if (this.pdfFile) {
                console.log('Fichier sélectionné :', this.pdfFile.name)
            }
        },

        async handleSubmit() {
            let pathPdf = ""
            let pdfIDWP = null
            const obj = this.subCategoriesAndSources[this.categorieSelected].subcats
            let keyFound = Object.keys(obj).find(
                key => obj[key].subcat_title === this.sousCategorieSelected
            )
            if (this.pdfFile) {
                const formData = new FormData();
                formData.append("file", this.pdfFile);
                try {
                    const response = await apiUpload.upload_file(formData)
                    if (response.data.source_url) {
                        pathPdf = response.data.source_url
                        pdfIDWP = response.data.id
                    }
                    else {
                        console.log("Soucis lors de la récupération du path du fichier")
                        console.log(response.data)
                        return
                    }
                }
                catch (error) {
                    console.log(error)
                    return
                }
            }

            let source = {
                "path_file": pathPdf,
                "url_file": this.pdfUrl,
                "id_subcategorie": keyFound,
                "tag": this.tag,
                "id_wp": pdfIDWP
            }
            const results_2 = await apiSource.add_source(source)
            source.id_categorie = this.categorieSelected
            source.source_id = results_2.data.id
            this.$emit('newFile', source)
            this.$emit('cancelSignal', !this.isOpen)
        }
    },

    components:{
        ModalComponent
    }
}

</script>

<style>
</style>