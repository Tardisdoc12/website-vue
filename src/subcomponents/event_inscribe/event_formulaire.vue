<template>
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

            <!-- Ajouter des dates -->
            <div>
                <div class="block font-medium">
                    Dupliquer l'évènement pour les dates :
                    <button 
                        type="button" 
                        :style="{
                            display: 'inline-block',
                            color: Couleurs.white,
                            padding: '0.5rem 1rem',
                            borderRadius: '0.375rem',
                            backgroundColor: Couleurs.main_blue
                        }"
                        @click="addRange" 
                        class="mt-1 text-blue-500"
                    >
                            <font-awesome-icon icon="fa-solid fa-plus" />
                    </button>
                </div>
                
                <div v-for="(range, index) in cloneDates" :key="index" class="flex gap-2 items-center" style="margin-bottom: 5px;">
                    <input type="datetime-local" v-model="range.start_date" class="border p-1" />
                    <input type="datetime-local" v-model="range.end_date" class="border p-1" />
                    <button
                        type="button"
                        :style="{
                            display: 'inline-block',
                            color: Couleurs.white,
                            padding: '0.5rem 1rem',
                            borderRadius: '0.375rem',
                            backgroundColor: Couleurs.main_red
                        }"
                        @click="removeRange(index)" 
                        class="text-red-500"
                    >
                        <font-awesome-icon icon="fa-solid fa-trash" />
                    </button>
                </div>
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
                    <option :value="Events.seance">Seance</option>
                    <option :value="Events.stage">Stage</option>
                    <option :value="Events.balade">Balade</option>
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
                    {{ buttonName }}
                </button>
            </div>
        </div>
    </form>
</template>

<script>
import eventsService from '@/javascript/api/axios_events.js';
import { Events } from "@/javascript/constants/events_type.js"
import { Couleurs } from "@/javascript/constants/colors"

export default {
    props: {
        onSuccess: {
            type: Function,
            default: null,
        },
        eventSelected: {
            type: Object,
            default: null,
        }
    },

    mounted() {
        if (this.eventSelected) {
            this.event = {...this.eventSelected}
            this.isUpdate = true
        }
    },

    data() {
        return {
            Couleurs,
            event: {
                title: '',
                startDate: '',
                endDate: '',
                description: '',
                place: '',
                categorie: '',
                subscribePlace: 1,
                nonsubscribePlace: 0,
            },
            isChecked:false || this?.eventSelected?.subscribePlace >= 0,
            Events: Events,
            cloneDates: [
            ],
            isUpdate:false,
        }
    },

    computed: {
        buttonName() {
            return this.isUpdate ? "Modifier l'évènement" : "Créer l'événement"
        },

        titleName() {
            return this.isUpdate ? "Modification d'évènement" : "Création d'évènement"
        },

        descriptionForm: {
            get() {
                return this.event.description
            },
            set(newValue) {
                this.event.description = newValue
            }
        },

        titleForm: {
            get() {
                return this.event.title
            },
            set(newValue) {
                this.event.title = newValue
            }
        },


        startDateForm: {
            get() {
                return this.event.startDate
            },
            set(newValue) {
                this.event.startDate = newValue
            }
        },

        endDateForm: {
            get() {
                return this.event.endDate
            },
            set(newValue) {
                this.event.endDate = newValue
            }
        },

        placeForm: {
            get() {
                return this.event.place
            },
            set(newValue) {
                this.event.place = newValue
            }
        },

        subscribePlaceForm: {
            get() {
                return this.event.subscribePlace
            },
            set(newValue) {
                this.event.subscribePlace = newValue
            }
        },

        categorieForm: {
            get() {
                return this.event.categorie
            },
            set(newValue) {
                this.event.categorie = newValue
            }
        },

        nonsubscribePlaceForm: {
            get() {
                return this.event.nonsubscribePlace
            },
            set(newValue) {
                this.event.nonsubscribePlace = newValue
            }
        }
    },

    methods: {
        addRange() {
            this.cloneDates.push({ start_date: '', end_date: '' });
        },

        removeRange(index) {
            this.cloneDates.splice(index, 1);
        },

        async createEvent(event) {
            if (event.endDate !== '') {
                if (new Date(event.startDate) >= new Date(event.endDate)) {
                    alert("La date de fin doit être après la date de début.")
                    return
                }
            }
            else {
                event.endDate = null;
            }

            const response = await eventsService.createEvent(event)
            return response;
        },

        async handleSubmit() {
            if (!this.isChecked){
                this.subscribePlaceForm = -1
            }
            
            if (!this.isUpdate){
                
                const response = await this.createEvent(this.event)
                if(response?.data?.id){
                    const event = {
                        ...this.event,
                        id : response.data.id,
                        post_id : response.data.post_id,
                        users: []
                    }
                    this.$emit("createEvents", event)
                }
                if (this.cloneDates.length > 0) {
                    for (const range of this.cloneDates) {
                        let new_event = {
                            ...this.event,
                            startDate: range.start_date,
                            endDate: range.end_date
                        }
                        let response_clone = await this.createEvent(new_event);
                        if(response_clone?.data?.id) {
                            new_event.id = response_clone.data.id
                            new_event.post_id = response_clone.data.post_id
                            new_event.users = []
                            this.$emit("createEvents", new_event)
                        }
                    }
                }

                this.event = {
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
            else {
                const response = await eventsService.updateEvent(this.event.event_id, this.event)
                if (this.cloneDates.length > 0) {
                    for (const range of this.cloneDates) {
                        let event_duplicate = {
                            ...this.event,
                            startDate: range.start_date,
                            endDate: range.end_date
                        }
                        let response_update = await this.createEvent(event_duplicate);
                        if(response_update?.data?.id){
                            event_duplicate.id = response_update.data.id
                            event_duplicate.post_id = response_update.data.post_id
                            event_duplicate.users = []
                            this.$emit("createEvents", event_duplicate)
                        }
                    }
                }
                alert("Évènement modifié avec succés !")
                this.$emit('cancelSignal', !this.isOpen)
            }
        }
    }
}
</script>

<style>

</style>
