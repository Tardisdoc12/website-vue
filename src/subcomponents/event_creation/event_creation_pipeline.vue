<template>
    <div style="margin-left: 20px; margin-right: 20px;margin-top: 10px;">
         <!-- Onglets de navigation -->
        <OngletsComponents
            :OngletList="OngletList"
            v-model:steps="steps"
        />

        <div v-if="!checkRequireField">
            <p style="color: red;">Veuillez remplir tous les champs requis.</p>
        </div>

        <EventGeneral
            v-if="steps == 0"
            v-model:titleForm="event.title"
            v-model:startDateForm="event.startDate"
            v-model:endDateForm="event.endDate"
            :editor="editor"
            v-model:currentColor="currentColor"
        />

        <EventKind
            v-if="steps == 1"
            v-model:categorieSelected="event.categorie"
            v-model:placeSelected="event.place"
            v-model:categorieToSelect="categorieSelectedForEvent"
            v-model:isCheckedSavePlace="isCheckedSavePlace",
            v-model:isCheckedPlace="isCheckPlace"
            :placesEvent="placesEvent"
        />

        <EventSlot
            v-if="steps == 2"
            v-model:subscribePlace="event.subscribePlace"
            v-model:nonsubscribePlace="event.nonsubscribePlace"
            v-model:attentePlace="event.attentePlace"
            :canAttente="categorieSelectedForEvent.liste_attente"
            :isFree="isFree"
            @next="handleNext"
        />

        <EventPayement
            v-if="steps == 3"
            :canAdherentPayement="categorieSelectedForEvent.adherent_payant"
            :canNonAdherentPayement="categorieSelectedForEvent.non_adherent_payant"
            v-model:EventPayementTitle="event.payementTitle"
            v-model:EventsPayementAdherent="event.payementAmountAdherent"
            v-model:EventsPayementNonAdherent="event.payementAmountNonAdherent"
        />

                
        <div style="margin-top:10px;margin-bottom:10px;" class="flex justify-center gap-3">
            <button
                type="button"
                class="button-cancel"
                @click="handleCancel"
            >
                Annuler
            </button>

            <button
                type="button"
                @click="handleCreate"
                class="button-confirm"
                :disabled="!checkRequireField"
            >
                {{ !isUpdate ? "Créer" : "Modifier"}}
            </button>
        </div>
    </div>
</template>

<script>
import { Editor } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import { Color } from '@tiptap/extension-color'
import { TextStyle } from '@tiptap/extension-text-style'
import { Underline } from '@tiptap/extension-underline'

import EventKind from './event_kind.vue';
import EventGeneral from './event_general.vue';
import EventSlot from './event_slot.vue';
import EventPayement from './event_payement.vue';
import OngletsComponents from '@/subcomponents/unitary_elements/onglets_components.vue';

import eventsService from '@/javascript/api/axios_events.js';
import placesApi from '@/javascript/api/axios_places.js'

function formatDateFr(dateString) {
    if (!dateString) return ''
    const date = new Date(dateString)
    if (isNaN(date.getTime())) return ''

    const day = String(date.getDate()).padStart(2, '0')
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const year = date.getFullYear()

    return `${day}/${month}/${year}`
}

export default {
    name: "EventCreationPipeline",

    signals:[
        'validate',
        'cancel'
    ],

    props: {
        onSuccess: {
            type: Function,
            default: null,
        },
        eventSelected: {
            type: Object,
            default: null,
        },
        placesEvent: {
            type: Array,
            default: () => [],
        },
    },

    mounted() {
       this.editor = new Editor({
            content: this.event.description || '<p>Écris ton texte ici...</p>',
            extensions: [StarterKit, TextStyle, Color, Underline],
        })
        if(this.eventSelected) {
            this.event = this.eventSelected
            this.descriptionForm = this.eventSelected.description
            this.isUpdate = true

            const categorieFound = this.$settings.categories.find(categorie => categorie.nom === this.eventSelected.categorie)
            if(categorieFound) {
                this.categorieSelectedForEvent = categorieFound
            }

        }
    },

    beforeUnmount() {
        if (this.editor) {
            this.editor.destroy()
        }
    },

        data() {
        return {
            editor: null,
            categorieSelectedForEvent: {},
            isCheckedSavePlace: false,
            isCheckPlace: false,
            event: {
                title: '',
                startDate: '',
                endDate: '',
                description: '',
                place: '',
                categorie: '',
                subscribePlace: -1,
                nonsubscribePlace: 0,
                attentePlace: 0,
                payementTitle: '',
                payementAmountAdherent: 0,
                payementAmountNonAdherent: 0,
            },
            isFree: false,
            isCheckedHelloAsso: false || (this?.eventSelected?.billeterie_id != null && this?.eventSelected?.billeterie_id != "0"),
            isUpdate: false,

            steps: 0,
            currentColor: 'var(--main-color)',
        }
    },

    computed: {
        checkRequireField(){
            const requiredFields = [
                this.event.categorie,
                this.event.place,
                this.event.title,
                this.event.startDate,
            ]
            for (const field of requiredFields) {
                if (!field) {
                    return false
                }
            }

            if (this.HasPayement) {
                const payementFields = [
                ]
                if (Boolean(+this.categorieSelectedForEvent.adherent_payant)) {
                    payementFields.push(this.event.payementAmountAdherent)
                }
                if (Boolean(+this.categorieSelectedForEvent.non_adherent_payant)) {
                    payementFields.push(this.event.payementAmountNonAdherent)
                }

                for (const field of payementFields) {
                    if (!field) {
                        return false
                    }
                }
            }

            return true
        },
        OngletList() {
            let tablist = ['Général', 'Catégorie & lieu', 'Places',]
            if (this.HasPayement) {
                tablist.push('Paiement')
            }

            return tablist
        },

        descriptionForm: {
            get() { return this.editor.getHTML() },
            set(newValue) { this.editor.commands.setContent(newValue) }
        },

        HasPayement: {
            get() {
                return Boolean(+this.categorieSelectedForEvent.adherent_payant) || Boolean(+this.categorieSelectedForEvent.non_adherent_payant)
            },
            set(val) {
                // This setter can be used if you want to update the underlying data when HasPayement changes
            }
        }
    },

    methods: {
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

            this.event.description = this.descriptionForm
            if (!this.event.payementTitle){
                this.event.payementTitle = this.event.title + " - " + formatDateFr(this.event.startDate)
            }
            const response = await eventsService.createEvent(event)
            return response;
        },

        formatDateFr(dateString) {
            if (!dateString) return ''
            const date = new Date(dateString)
            if (isNaN(date.getTime())) return ''

            const day = String(date.getDate()).padStart(2, '0')
            const month = String(date.getMonth() + 1).padStart(2, '0')
            const year = date.getFullYear()

            return `${day}/${month}/${year}`
        },


        async handleCreate() {
            const places = this.placesEvent.filter(place => place.name === this.event.place);
            if (!places.length && this.isCheckedSavePlace) {
                const response = await placesApi.add_place({ name: this.event.place });
                if (response?.data?.success) {
                    this.$emit('update:placeSelected', response.data.places);
                }
            }
            let response;
            if(!this.isUpdate){
                response = await this.createEvent(this.event)
                if (this.onSuccess) {
                    await this.onSuccess()
                }
            }
            else{
                response = await eventsService.updateEvent(this.event.event_id, this.event)
                alert("Évènement modifié avec succés !")
            }
            this.event.event_id = response?.data?.id
            this.$emit('validate', this.event)
            this.$emit('cancel')
        },

        handleCancel() {
            this.$emit('cancel')
        }
    },

    components: {
        EventGeneral,
        EventKind,
        EventSlot,
        EventPayement,
        OngletsComponents,
    }
}
</script>