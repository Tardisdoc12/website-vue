<template>
    <div style="margin-left: 20px; margin-right: 20px;margin-top: 10px;">
         <!-- Onglets de navigation -->
        <div class="steps-tabs">
            <button
                v-for="(tab, index) in OngletList"
                :key="index"
                type="button"
                class="step-tab"
                :class="{ active: steps === index }"
                @click="goToStep(index)"
            >
                <span class="step-tab-index">{{ index + 1 }}</span>
                {{ tab }}
            </button>
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
    </div>
</template>

<script>
import { Editor } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import { Color } from '@tiptap/extension-color'
import { TextStyle } from '@tiptap/extension-text-style'
import { Underline } from '@tiptap/extension-underline'

import EventKind from '@/subcomponents/event_creation/event_kind.vue';
import EventGeneral from '@/subcomponents/event_creation/event_general.vue';
import EventSlot from '@/subcomponents/event_creation/event_slot.vue';
import EventPayement from '@/subcomponents/event_creation/event_payement.vue';

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
        'update:eventSelected',
    ],

    props: {
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
        eventToDuplicate: {
            get() {
                return this.event
            },
            set(newValue) {
                if (newValue.endDate !== '') {
                    if (new Date(newValue.startDate) >= new Date(newValue.endDate)) {
                            return
                    }
                }
                else {
                    newValue.endDate = null;
                }

                newValue.description = this.descriptionForm
                if (!newValue.payementTitle){
                    newValue.payementTitle = newValue.title + " - " + formatDateFr(newValue.startDate)
                }

                this.event = { ...newValue }
                this.$emit('update:eventSelected', this.event)
            }

        },

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
                if (this.categorieSelectedForEvent.adherent_payant) {
                    payementFields.push(this.event.payementAmountAdherent)
                }
                if (this.categorieSelectedForEvent.non_adherent_payant) {
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
                return this.categorieSelectedForEvent.adherent_payant || this.categorieSelectedForEvent.non_adherent_payant
            },
            set(val) {
                // This setter can be used if you want to update the underlying data when HasPayement changes
            }
        }
    },

    methods: {
        goToStep(index) {
            this.steps = index
        },
    },

    components: {
        EventGeneral,
        EventKind,
        EventSlot,
        EventPayement,
    }
}
</script>

<style scoped>
.steps-tabs {
    display: flex;
    gap: 4px;
    margin-bottom: 20px;
    border-bottom: 2px solid #e0e0e0;
}

.step-tab {
    appearance: none;
    background: none;
    border: none;
    padding: 10px 16px;
    font-size: 0.9rem;
    color: #888;
    cursor: pointer;
    border-bottom: 2px solid transparent;
    transition: color 0.15s ease, border-color 0.15s ease;
    display: flex;
    align-items: center;
    gap: 6px;
}

.step-tab:hover {
    color: var(--main-color, #333);
}

.step-tab.active {
    color: var(--main-color, #333);
    font-weight: 600;
    border-bottom-color: var(--main-color, #333);
}

.step-tab-index {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #e0e0e0;
    color: #666;
    font-size: 0.75rem;
    font-weight: 600;
}

.step-tab.active .step-tab-index {
    background: var(--main-color, #333);
    color: white;
}
</style>