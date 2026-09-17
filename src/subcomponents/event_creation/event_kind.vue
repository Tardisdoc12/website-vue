<template>
    <!-- Catégorie -->
    <div style="margin-bottom:10px;">
        <label class="block font-medium">
            Catégorie <span style="color:darkred">*</span>
        </label>
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
        <label class="block font-medium">Lieu <span style="color:darkred">*</span></label>
        <input
            v-model="place"
            type="text"
            class="w-full border p-1 rounded"
            required
        />
    </div>
    <div style="margin-bottom:10px;" v-else>
        <label class="block font-medium">Lieu <span style="color:darkred">*</span></label>
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
        <input type="checkbox" v-model="isCheckedSavePlaceComp"/>
    </div>

</template>

<script>
import placesApi from '@/javascript/api/axios_places.js'

export default {
    name: "EventKind",

    signals:[
        'update:categorieSelected',
        'update:placeSelected',
        'update:categorieToSelect',
        'update:isCheckedSavePlace',
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
        },

        isCheckedSavePlace: {
            type: Boolean,
            default: false,
        }
    },

    data() {
        console.log(this.categorieToSelect)
        return {
            settingsCategories: this.$settings.categories,
            isCheckedPlace: false || this.placeSelected === '',
            isCheckedSavePlaceBool: this.isCheckedSavePlace,
            categorieToSelectEvent: this.categorieToSelect,
        }
    },

    computed: {
        isCheckedSavePlaceComp: {
            get() { return this.isCheckedSavePlace },
            set(val) { this.$emit('update:isCheckedSavePlace', val) }
        },

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
}
</script>