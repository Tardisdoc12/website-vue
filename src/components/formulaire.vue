<template>
    <Modal
        :title="title"
        :isCancel="isOpen"
        @changeBool="Cancel"
    >
        <form @submit.prevent="handleSubmit" class="space-y-4">
            <div style="margin-left: 20px; margin-right: 20px;margin-top: 10px;">
                <!-- Titre -->
                <div class="flex flex-col gap-1" style="margin-bottom:10px;">
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
                <div style="margin-bottom:10px;">
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
                <div style="margin-bottom:10px;">
                    <label class="block font-medium">Date et heure de fin</label>
                    <input v-model="endDateForm" type="datetime-local" class="w-full border p-1 rounded"/>
                </div>

                <!-- Description -->
                <div style="margin-bottom:10px;">
                    <label class="block font-medium">Description</label>
                    <textarea v-model="descriptionForm" class="w-full border p-1 rounded" rows="4" required></textarea>
                </div>

                <!-- Catégorie -->
                <div style="margin-bottom:10px;">
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
                <div style="margin-bottom:10px;">
                    <label class="block font-medium">Lieu</label>
                    <input
                        v-model="placeForm"
                        type="text"
                        class="w-full border p-1 rounded"
                        required
                    />
                </div>


                <!-- limité dans le nombre de place -->
                <div class="flex items-center space-x-2" style="margin-bottom:10px;">
                    <label class="font-medium" style="padding: 2px;">Nombre de place limité pour les adhérents :</label>
                    <input type="checkbox" v-model="isChecked"/>
                </div>

                <!-- Nombre de places -->
                <div v-if="isChecked" style="margin-bottom:10px;">
                    <label class="block font-medium">Nombre de places pour les adhérents</label>
                    <input v-model.number="subscribePlaceForm" type="number" min="1" class="w-full border p-1 rounded" required />
                </div>

                <!-- Nombre de places -->
                <div style="margin-bottom:10px;">
                    <label class="block font-medium">Nombre de places pour les non-adhérents</label>
                    <input v-model.number="nonsubscribePlaceForm" type="number" min="0" class="w-full border p-1 rounded" required />
                </div>

                <!-- Bouton -->
                <div class="flex justify-center items-center" style="margin-bottom:10px;">
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600" >
                        Créer l'événement
                    </button>
                </div>
            </div>
        </form>
    </Modal>
</template>

<script>
import Modal from "./subcomponents/unitary_elements/modalComponent.vue"
import eventsService from '../javascript/axios_events.js';
import Categories from "../javascript/constants.js"

export default {
    props: {
        onSuccess: {
            type: Function,
            default: null,
        }
    },

    data() {
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
            isOpen: true,
            title: "Création d'évènement",
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

        Cancel() {
            this.$emit('cancelSignal', !this.isOpen)
        },

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
            if (this.onSuccess) {
                await this.onSuccess()
            }
        }
    },

    components: {
        Modal
    }
}
</script>

<style>

</style>
