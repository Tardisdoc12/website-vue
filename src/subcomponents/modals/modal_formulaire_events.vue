<template>
    <Modal
        :title="titleName"
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

                <!-- Ajouter des dates -->
                <div>
                    <div class="block font-medium">
                        Dupliquer l'évènement pour les dates :
                        <button 
                            type="button" 
                            :style="{
                                display: inline-block,
                                color: white,
                                padding: '0.5rem 1rem',
                                borderRadius: '0.375rem',
                                backgroundColor: '#00BFFF'
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
                                display: inline-block,
                                color: white,
                                padding: '0.5rem 1rem',
                                borderRadius: '0.375rem',
                                backgroundColor: '#FF0000'
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
    </Modal>
</template>

<script>
import Modal from "@/subcomponents/unitary_elements/modalComponent.vue"
import eventsService from '@/javascript/api/axios_events.js';
import { Events } from "@/javascript/constants/events_type.js"

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
            this.form = {...this.eventSelected}
            this.isUpdate = true
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
            isChecked:false || this?.eventSelected?.subscribePlace >= 0,
            Events: Events,
            cloneDates: [
            ],
            isUpdate:false,
            isOpen: true,
            title: "Création d'évènement",
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
        addRange() {
            this.cloneDates.push({ start_date: '', end_date: '' });
        },

        removeRange(index) {
            this.cloneDates.splice(index, 1);
        },
        
        Cancel() {
            this.$emit('cancelSignal', !this.isOpen)
        },

        async createEvent(form) {
            if (form.endDate !== '') {
                    if (new Date(form.startDate) >= new Date(form.endDate)) {
                        alert("La date de fin doit être après la date de début.")
                        return
                    }
                }
                else {
                    form.endDate = null;
                }

                const response = await eventsService.createEvent(form)
                return response;
        },

        async handleSubmit() {
            if (!this.isChecked){
                this.subscribePlaceForm = -1
            }
            
            if (!this.isUpdate){
                
                const response = await this.createEvent(this.form)
                if (this.cloneDates.length > 0) {
                    for (const range of this.cloneDates) {
                        await this.createEvent({
                            ...this.form,
                            startDate: range.start_date,
                            endDate: range.end_date
                        });
                    }
                }
                alert("Événement(s) créé avec succès !")

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
            else {
                const response = await eventsService.updateEvent(this.form.event_id, this.form)
                if (this.cloneDates.length > 0) {
                    for (const range of this.cloneDates) {
                        await this.createEvent({
                            ...this.form,
                            startDate: range.start_date,
                            endDate: range.end_date
                        });
                    }
                }
                alert("Évènement modifié avec succés !")
                this.$emit('cancelSignal', !this.isOpen)
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
