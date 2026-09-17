<template>
    <form @submit.prevent="handleSubmit" class="space-y-4">
        <!-- Catégorie -->
        <div style="margin-bottom:10px;">
            <select
                v-model="categorieComp"
                class="w-full border p-1 rounded"
                required
            >
                <option disabled value="">-- Choisir une catégorie --</option>
                <option
                    v-for="cat in settingsCategories"
                    :key="cat.nom"
                    :value="cat"
                >
                    {{ cat.nom }}
                </option>
            </select>
        </div>

        <!-- Ajout de nouveau lieu -->
        <div style="margin-bottom: 10px;display: flex; align-items: center; gap: 8px;">
            <label class="block font-medium">Nouveau lieu</label>
            <input type="checkbox" v-model="isCheckedPlace"/>
        </div>

        <!-- Lieu -->
        <div style="margin-bottom:10px;" v-if="isCheckedPlace">
            <label class="block font-medium">Lieu</label>
            <input
                v-model="place"
                type="text"
                class="w-full border p-1 rounded"
                required
            />
        </div>
        <div style="margin-bottom:10px;" v-else>
            <label class="block font-medium">Lieu</label>
            <select
                v-model="place"
                class="w-full border p-1 rounded"
                required
            >
                <option disabled value="">-- Choisir un lieu --</option>
                <option v-for="place in placesEvent" :key="place.id" :value="place.name">
                    {{ place.name }}
                </option>
            </select>
        </div>

        <div v-if="isCheckedPlace" style="margin-bottom: 10px;display: flex; align-items: center; gap: 8px;">
            <label class="block font-medium">Sauvegarder le nouveau lieu</label>
            <input type="checkbox" v-model="isCheckedSavePlace"/>
        </div>

        <!-- Submit Button -->
        <div style="margin-top:10px;margin-bottom:10px;" class="flex justify-center gap-3">
            <button
                type="button"
                class="appearance-none button-base"
                @click="handlePrevious"
            >
                Précédent
            </button>

            <button type="submit" class="appearance-none button-base">
                Suivant
            </button>
        </div>
    </form>
</template>

<script>
import placesApi from '@/javascript/api/axios_places.js'

export default {
    name: "EventKind",

    signals:[
        'update:categorieSelected',
        'update:placeSelected',
        'update:categorieToSelect',
        'next',
        'previous',
    ],

    props: {
        categorieToSelect: {
            type: Object,
            default: () => ({}),
        },

        placesEvent: {
            type: Array,
            default: () => [],
        },

        settingsCategories: {
            type: Array,
            default: () => [],
        },

        categorieSelected: {
            type: String,
            default: '',
        },

        placeSelected: {
            type: String,
            default: '',
        }
    },

    data() {
        return {
            settingsCategories: this.$settings.categories,
            isCheckedPlace: false || this.placeSelected === '',
            isCheckedSavePlace: false,
            categorieToSelectEvent: this.categorieToSelect,
        }
    },

    computed: {
        categorieComp: {
            get() { return this.categorieToSelectEvent },
            set(val) {
                this.categorieToSelectEvent = val
                this.$emit('update:categorieToSelect', val)
                this.$emit('update:categorieSelected', val?.nom ?? '')
            }
        },

        place: {
            get() { return this.placeSelected },
            set(val) { this.$emit('update:placeSelected', val) }
        }
    },

    methods:{
        selectCanAttente(categorie) {
            this.categorieComp = categorie
        },

        async handleSubmit() {
            const places = this.placesEvent.filter(place => place.name === this.place);
            if (!places.length && this.isCheckedSavePlace) {
                const response = await placesApi.add_place({ name: this.place });
                if (response?.data?.success) {
                    this.$emit('update:placeSelected', response.data.places);
                }
            }
            this.$emit('next')
        },

        handlePrevious() {
            this.$emit('previous')
        }
    }
}
</script>