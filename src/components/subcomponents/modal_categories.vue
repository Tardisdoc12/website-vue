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

                <div
                    class="flex flex-col gap-1"
                >
                    <label class="block font-medium">
                        Nom de la sous-catégorie
                    </label>
                    <input
                        v-model="sousCategorieSelected"
                        type="text"
                        class="w-full border p-1 rounded"
                        required
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
import apiSource from '@/javascript/axios_sources';
import ModalComponent from './unitary_elements/modalComponent.vue';


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
            const obj = this.subCategoriesAndSources[this.categorieSelected].subcats
            let keyFound = Object.keys(obj).find(
                key => obj[key].subcat_title === this.sousCategorieSelected
            )
            if(keyFound) {
                console.log(keyFound)
                this.$emit('cancelSignal', !this.isOpen)
            }
            else{
                let subcategorieToSend = {
                    "id_categorie": this.categories.indexOf(this.categorieSelected) + 1,
                    "title" : this.sousCategorieSelected,
                }
                console.log("Subcategorie to send:", subcategorieToSend)
                const results = await apiSource.add_subcategorie(subcategorieToSend)
                
                if (results.status !== 200) {
                    this.$emit('newCategorie', this.sousCategorieSelected)
                }
                keyFound = results.data.id
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