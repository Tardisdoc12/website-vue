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
                        <option :key="categorie" v-for="categorie in categories">{{ categorie }}</option>
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
                            <option :key="sousCategorie" v-for="sousCategorie in sousCategories">{{ sousCategorie }}</option>
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
import ModalComponent from './unitary_elements/modalComponent.vue';

export default {
    props: {
        sousCategories: {
            type: String,
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

        handleSubmit() {
            this.$emit('newCategorie', this.sousCategorieSelected)
        }
    },

    components: {
        ModalComponent
    }
}
</script>

<style>

</style>