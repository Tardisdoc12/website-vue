<template>
    <ModalComponent
        :title="'Ajouter une catégorie'"
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

                <!-- Gestion de la sous-categorie -->
                <div v-if="categorieSelected">
                    <!-- La sous-Categories existe déjà? -->
                    <div class="flex flex-col gap-1" style="margin-bottom: 10px;margin-top: 10px;">
                        <label class="block font-medium">
                            La sous-catégorie existe-t-elle?
                        </label>

                        <div style="margin-top: 5px;" class="flex items-center gap-2">
                            <label for="one">Sous-catégorie préexistante :</label>
                            <input type="radio" id="one" value="1" v-model="isSousCategorieExists">
                        </div>
                        
                        <div style="margin-top: 5px;" class="flex items-center gap-2">
                            <label for="two">Nouvelle Catégorie :</label>
                            <input type="radio" id="two" value="2" v-model="isSousCategorieExists">
                        </div>
                    </div>

                    <!-- Si la sous Categorie existe alors on Séléctionne celle voulue -->
                    <div 
                        v-if="isSousCategorieExists == 1"
                        class="flex flex-col gap-1"
                    >
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

                    <!-- Sinon on demande le nom -->
                    <div
                        v-if="isSousCategorieExists == 2"
                        class="flex flex-col gap-1"
                    >
                        <label class="block font-medium">
                            Nom de la sous-catégorie
                        </label>
                        <input
                            v-if="isSousCategorieExists == 2"
                            v-model="sousCategorieSelected"
                            type="text"
                            class="w-full border p-1 rounded"
                            required
                        />
                    </div>
                </div>

                <!-- Le type de fichier qu'on veut ajouter -->
                <div
                    v-if="sousCategorieSelected"
                >
                    <div class="flex flex-col gap-1">
                        <label>{{ "Fichier ou url" }}</label>
                        <select v-model="typeAdd">
                            <option disabled value="">Choisissez</option>
                            <option>{{ "Fichier" }}</option>
                            <option>{{ "Url" }}</option>
                            <option>{{ "Aucun" }}</option>
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

                <!-- Le Bouton de validation -->
                <div class="flex items-center justify-center " style="margin-bottom:10px;margin-top: 15px;">
                    <button
                        :disabled="isDisable"
                        type="submit"
                        class="px-4 py-2 rounded-lg font-medium text-white transition"
                        :class="isDisabled ? 'bg-gray-400 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700'"
                    >
                        Ajouter la catégorie
                    </button>
                </div>
            </div>
        </form>
    </ModalComponent>
</template>

<script>
import apiSource from '@/javascript/axios_sources';
import ModalComponent from './unitary_elements/modalComponent.vue';
import apiUpload from '@/javascript/axios_upload'

export default {
    props: {
        subCategoriesAndSources: {
            type: Object,
            required: true
        }
    },

    data() {
        return {
            isOpen: true,
            categories: [
                "Fiches et Exos",
                "Parcours d'entrainement",
                "Liens Utiles"
            ],
            categorieSelected: null,
            sousCategorieSelected: "",
            isSousCategorieExists: "",
            typeAdd: "",
            pdfFile: null,
            pdfUrl: null,
            tag: null,
        }
    },

    computed: {
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

    methods: {
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
            const obj = this.subCategoriesAndSources[this.categorieSelected].subcats
            let keyFound = Object.keys(obj).find(
                key => obj[key].subcat_title === this.sousCategorieSelected
            )
            if(keyFound) {
                console.log(keyFound)
            }
            else{
                let subcategorieToSend = {
                    "id_categorie": this.categories.indexOf(this.categorieSelected) + 1,
                    "title" : this.sousCategorieSelected,
                }
                const results = await apiSource.add_subcategorie(subcategorieToSend)
                
                if (results.status !== 200) {
                    this.$emit('newCategorie', this.sousCategorieSelected)
                }
                keyFound = results.data.id
            }

            if(this.typeAdd !== "Aucun") {
                // on gère le pdf pour l'upload
                let pathPdf = ""
                if (this.pdfFile) {
                    const formData = new FormData();
                    formData.append("file", this.pdfFile);
                    try {
                        const response = await apiUpload.upload_file(formData)
                        if (response.data.source_url) {
                            pathPdf = response.data.source_url
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

                const source = {
                    "path_file": pathPdf,
                    "url_file": this.pdfUrl,
                    "id_subcategorie": keyFound,
                    "tag": this.tag,
                }
                const results_2 = await apiSource.add_source(source)
                console.log(results_2)
            }
        }
    },

    components: {
        ModalComponent
    }
}
</script>

<style>

</style>