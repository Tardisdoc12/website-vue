<template>
    <div class="max-w-md mx-auto p-4 bg-white shadow rounded">
        <h2 class="text-xl font-bold mb-4">Créer un événement</h2>
        <form @submit.prevent="handleSubmit" class="space-y-4">

            <!-- Titre -->
            <div class="flex flex-col gap-1">
                <label class="block font-medium">
                    Titre
                </label>
                <input
                    v-model="titleForm"
                    type="text"
                    class="w-full border p-1 rounded"
                    required
                />
            </div>

            <!-- Date de début -->
            <div>
                <label class="block font-medium">
                    Date et heure de début
                </label>
                <input
                    v-model="startDateForm"
                    type="datetime-local"
                    class="w-full border p-1 rounded"
                    required 
                />
        </div>

            <!-- Date de fin -->
            <div>
                <label class="block font-medium">Date et heure de fin</label>
                <input v-model="endDateForm" type="datetime-local" class="w-full border p-1 rounded"/>
            </div>

            <!-- Description -->
            <div>
                <label class="block font-medium">Description</label>
                <textarea v-model="descriptionForm" class="w-full border p-1 rounded" rows="4" required></textarea>
            </div>

            <!-- Catégorie -->
            <div>
                <label class="block font-medium">Catégorie</label>
                <select
                    v-model="categorieForm"
                    class="w-full border p-1 rounded"
                    required
                >
                    <option disabled value="">-- Choisir une catégorie --</option>
                    <option :value="Categories.seance">Seance</option>
                    <option :value="Categories.stage">Stage</option>
                    <option :value="Categories.balade">Balade</option>
                </select>
            </div>

        <!-- Lieu -->
            <div>
                <label class="block font-medium">Lieu</label>
                <input
                    v-model="placeForm"
                    type="text"
                    class="w-full border p-1 rounded"
                    required
                />
            </div>


            <!-- limité dans le nombre de place -->
            <div class="flex items-center space-x-2">
                <label class="font-medium" style="padding: 2px;">Nombre de place limité pour les adhérents :</label>
                <input type="checkbox" v-model="isChecked"/>
            </div>

            <!-- Nombre de places -->
            <div v-if="isChecked">
                <label class="block font-medium">Nombre de places pour les adhérents</label>
                <input v-model.number="subscribePlaceForm" type="number" min="1" class="w-full border p-1 rounded" required />
            </div>

            <!-- Nombre de places -->
            <div>
                <label class="block font-medium">Nombre de places pour les non-adhérents</label>
                <input v-model.number="nonsubscribePlaceForm" type="number" min="0" class="w-full border p-1 rounded" required />
            </div>

            <!-- Bouton -->
            <div class="flex justify-between items-center">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Créer l'événement
                </button>
                <button
                    type="button"
                    class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600"
                    @click="$emit('updateCancelBool', !cancelBool)"
                >
                    Annuler
                </button>
            </div>
        </form>
    </div>
</template>

<script>
import eventsService from '../javascript/axios_events.js';
import Categories from "../javascript/constants.js"

export default {

    props:{
        cancelBool: {
            required: true
        }
    },

    data() {
        console.log("on a les elements: ",Categories)
        return {
            form: {
                title: '',
                startDate: '',
                endDate: '',
                description: '',
                place: '',
                categorie: '',
                subscribePlace: 1,
                nonsubscribePlace: 0,
            },
            isChecked:false,
            Categories,
        }
    },

    computed: {
        descriptionForm: {
            get() {
                return this.form.description
            },
            set(newValue) {
                this.form.description = newValue
            }
        },

        titleForm: {
            get() {
                return this.form.title
            },
            set(newValue) {
                this.form.title = newValue
            }
        },


        startDateForm: {
            get() {
                return this.form.startDate
            },
            set(newValue) {
                this.form.startDate = newValue
            }
        },

        endDateForm: {
            get() {
                return this.form.endDate
            },
            set(newValue) {
                this.form.endDate = newValue
            }
        },

        placeForm: {
            get() {
                return this.form.place
            },
            set(newValue) {
                this.form.place = newValue
            }
        },

        subscribePlaceForm: {
            get() {
                return this.form.subscribePlace
            },
            set(newValue) {
                this.form.subscribePlace = newValue
            }
        },

        categorieForm: {
            get() {
                return this.form.categorie
            },
            set(newValue) {
                this.form.categorie = newValue
            }
        },

        nonsubscribePlaceForm: {
            get() {
                return this.form.nonsubscribePlace
            },
            set(newValue) {
                this.form.nonsubscribePlace = newValue
            }
        }
    },

    methods: {
        async handleSubmit() {
            if (!this.isChecked){
                this.subscribePlaceForm = -1
            }

            if (this.endDateForm !== '') {
                if (new Date(this.startDateForm) >= new Date(this.endDateForm)) {
                    alert("La date de fin doit être après la date de début.")
                    return
                }
            }
            else {
                this.endDateForm = new Date(this.startDateForm);
                this.endDateForm.setHours(23, 59, 0, 0);
            }
            
            console.log("Formulaire soumis :", this.form)
            alert("Événement créé avec succès !")
            const response = await eventsService.createEvent(this.form)
            this.form = {
                        title: '',
                        startDate: '',
                        endDate: '',
                        description: '',
                        place: '',
                        categorie: '',
                        subscribePlace: 1,
                        nonsubscribePlace: 0,
                    }
        }
    }
}
</script>

<style>

</style>
