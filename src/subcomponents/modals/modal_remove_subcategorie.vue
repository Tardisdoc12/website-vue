<template>
    <ModalComponent
        :title="'Supprimer une Sous-catégorie'"
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
                            :value="keySub"
                        >
                            {{ sousCategorie.subcat_title }}
                        </option>
                    </select>
                </div>

                <!-- Le Bouton de validation -->
                <div class="flex items-center justify-center " style="margin-bottom:10px;margin-top: 15px;">
                    <button
                        :disabled="isDisable"
                        type="submit"
                        class="px-4 py-2 rounded-lg font-medium text-white transition"
                        :class="isDisabled ? 'bg-gray-400 cursor-not-allowed' : 'bg-red-600 hover:bg-red-700'"
                    >
                        Supprimer la sous-catégorie
                    </button>
                </div>

            </div>
        </form>
    </ModalComponent>
</template>

<script>
import apiSource from '@/javascript/api/axios_sources';
import ModalComponent from '@/subcomponents/unitary_elements/modalComponent.vue';

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
        }
    },
    computed: {
        isDisable() {
            if(this.sousCategorieSelected === "") {
                return true
            }
            return false
        },
    },

    methods: {
        Cancel() {
            this.$emit('cancelSignal', !this.isOpen)
        },
        async handleSubmit() {
            if(this.subCategoriesAndSources[this.categorieSelected].subcats[this.sousCategorieSelected].files.length > 0) {
                alert("Vous ne pouvez pas supprimer une sous-catégorie qui contient des fichiers. Veuillez d'abord supprimer les fichiers associés.")
                return
            }
            else {
                if (confirm("Êtes-vous sûr de vouloir supprimer cette sous-catégorie ?")) {
                    const response = await apiSource.delete_subcategorie(this.sousCategorieSelected)
                    if(response.status === 200) {
                        this.$emit('deleteSubcategorie', this.sousCategorieSelected)
                        this.Cancel()
                    }
                }
            }
        },
    },
    components: {
        ModalComponent
    },
}

</script>

<style>
</style>