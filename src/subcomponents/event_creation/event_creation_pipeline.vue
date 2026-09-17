<template>
    <div style="margin-left: 20px; margin-right: 20px;margin-top: 10px;">
        <EventGeneral
            v-if="steps == 0"
            v-model:titleForm="event.title"
            v-model:startDateForm="event.startDate"
            v-model:endDateForm="event.endDate"
            :editor="editor"
            v-model:currentColor="currentColor"
            @next="handleNext"
            @cancel="handleCancel"
        />

        <EventKind
            v-if="steps == 1"
            v-model:categorieSelected="event.categorie"
            v-model:placeSelected="event.place"
            v-model:categorieToSelect="categorieSelectedForEvent"
            :placesEvent="placesEvent"
            @next="handleNext"
            @previous="handlePrevious"
        />

        <EventSlot
            v-if="steps == 2"
            v-model:subscribePlace="event.subscribePlace"
            v-model:nonsubscribePlace="event.nonsubscribePlace"
            v-model:attentePlace="event.attentePlace"
            :canAttente="categorieSelectedForEvent.liste_attente"
            :isFree="isFree"
            @next="handleNext"
            @create="handleCreate"
            @previous="handlePrevious"
        />

        <EventPayement
            v-if="steps == 3"
            :canAdherentPayement="categorieSelectedForEvent.adherent_payant"
            :canNonAdherentPayement="categorieSelectedForEvent.non_adherent_payant"
            v-model:payementTitle="event.payementTitle"
            v-model:payementAmountAdherent="event.payementAmountAdherent"
            v-model:payementAmountNonAdherent="event.payementAmountNonAdherent"
            @create="handleCreate"
            @previous="handlePrevious"
        />
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

import eventsService from '@/javascript/api/axios_events.js';

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
        billeteries: {
            type: Array,
            default: () => [],
        },
    },

    watch: {
        eventSelected: {
            immediate: true,
            handler(newVal) {
                if (newVal) {
                    this.event = {
                        ...newVal,
                        startDate:   this.formatDateForInput(newVal.startDate),
                        endDate:     this.formatDateForInput(newVal.endDate),
                        description: newVal.description ?? '',
                    }
                    this.isUpdate = true
                    this.isChecked = newVal.subscribePlace >= 0
                    this.isCheckedAttente = newVal.attentePlace > 0

                    // Mettre à jour le contenu de l'éditeur s'il existe déjà
                    if (this.editor) {
                        this.editor.commands.setContent(this.event.description || '<p>Écris ton texte ici...</p>')
                    }
                }
            }
        }
    },

    mounted() {
       this.editor = new Editor({
            content: this.event.description || '<p>Écris ton texte ici...</p>',
            extensions: [StarterKit, TextStyle, Color, Underline],
        })
        if(this.eventSelected) {
            this.event.place = this.eventSelected.place
            // const filteredPlaces = this.placesEvent.filter(place => place.name === this.eventSelected.place)
            // if(filteredPlaces.length === 0){
            //     this.isCheckedPlace = true
            //     this.isCheckedSavePlace = true
            // }
            // else{
            //     this.isCheckedPlace = false
            //     this.isCheckedSavePlace = false
            // }
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
            event: {
                title: '',
                startDate: '',
                endDate: '',
                description: '',
                place: '',
                categorie: '',
                subscribePlace: 1,
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
        descriptionForm: {
            get() { return this.editor.getHTML() },
            set(newValue) { this.editor.commands.setContent(newValue) }
        }
    },

    methods: {
        handleNext() {
            if (this.event.description !== this.descriptionForm) {
                this.event.description = this.descriptionForm
            }
            this.isFree = !(this.categorieSelectedForEvent.adherent_payant || this.categorieSelectedForEvent.non_adherent_payant)
            
            this.steps++
        },

        handlePrevious() {
            this.steps--
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


        async handleCreate() {
            if(!this.isUpdate){
                const response = await this.createEvent(this.event)
                if (this.onSuccess) {
                    await this.onSuccess()
                }
            }
            else{
                const response = await eventsService.updateEvent(this.event.event_id, payload)
                alert("Évènement modifié avec succés !")
            }
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
    }
}
</script>