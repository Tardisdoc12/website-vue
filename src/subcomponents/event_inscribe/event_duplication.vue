<template>
<!-- On va mettre en place la duplication d'événement -->
<!-- Il nous faut : 1) l'event de base + 2) les nouvelles dates + 3) la nouvelle billeterie + 4) la liste d'attente -->

    <div class="block font-medium flex justify-left" style="margin-left:10px;margin-top:10px;">
        Dupliquer l'évènement pour les dates :
        <button 
            type="button"
            @click="addRange" 
            class="appearance-none button-base"
        >
            <font-awesome-icon icon="fa-solid fa-plus" />
        </button>
    </div>
    
    <!-- Indicateur d'étapes -->
    <div class="steps-indicator" style="margin-bottom: 10px;">
        <div
            v-for="(p, index) in listEvents"
            :key="index"
            class="step-dot"
            :class="{ active: currentStep === index, done: currentStep > index }"
            @click="currentStep > index ? currentStep = index : null"
        >
            <span v-if="currentStep > index">✓</span>
            <span v-else>{{ index + 1 }}</span>
        </div>

        <button
            v-if="currentStep > 0"
            type="button"
            :style="{
                '--btn-bg': Couleurs.main_red,
                '--btn-hover-bg': Couleurs.dark_red
            }"
            @click="removeRange(currentStep)"
            class="appearance-none button-base"
        >
            <font-awesome-icon icon="fa-solid fa-trash" />
        </button>
    </div>

    <div class="flex gap-2 items-center" style="margin-left:10px;justify-content: center;">
        <!-- Date de début -->
        <div style="margin-bottom:10px;">
            <label class="block font-medium">
                Date et heure de début
            </label>
            <input
                v-model="startDateComp"
                type="datetime-local"
                class="w-full border p-1 rounded"
                required 
            />
        </div>

        <!-- Date de fin -->
        <div style="margin-bottom:10px;">
            <label class="block font-medium">Date et heure de fin</label>
            <input v-model="endDateComp" type="datetime-local" class="w-full border p-1 rounded"/>
        </div>
    </div>


    <div class="flex" style="margin-bottom:10px;justify-content: center;margin-left: 10px;margin-right: 10px;" v-if="categorieForm === Events.stage">
        <label class="block font-medium">Lien billetterie (optionnel)</label>
        <input
            v-model="urlBilleterieComp"
            type="url"
            class="w-full border p-1 rounded"
            placeholder="https://example.com/billetterie"
        />
    </div>

    <div class="flex gap-2" style="margin-bottom: 10px;justify-content: center;margin-left: 10px;margin-right: 10px;" v-if="categorieForm !== Events.balade">
        <label class="block font-medium">Ajouter une liste d'attente?</label>
        <input type="checkbox" v-model="isCheckedAttente"/>
    </div>

    <div class="flex gap-2" style="margin-bottom: 10px;justify-content: center;margin-left: 10px;margin-right: 10px;" v-if="categorieForm !== Events.balade && isCheckedAttente">
        <label class="block font-medium">Nombre de place dans la liste d'attente</label>
        <input type="number" v-model.number="attentePlaceComp" min="0" class="w-full border p-1 rounded" required />
    </div>

    <div class="flex justify-center gap-3" style="margin-right:10px;margin-bottom:10px;">
        <button
            type="button"
            @click="previousStep"
            :disabled="currentStep === 0"
            class="appearance-none button-base"
            :style="{
                '--btn-bg': Couleurs.main_green,
                '--btn-hover-bg': Couleurs.dark_green
            }"
        >
            Précédent
        </button>
        <button
            type="button"
            @click="Cancel"
            class="appearance-none button-base"
            :style="{
                '--btn-bg': Couleurs.main_red,
                '--btn-hover-bg': Couleurs.dark_red
            }"
        >
            Annuler
        </button>
        <button
            v-if="!isLastStep"
            type="button"
            @click="nextStep"
            class="appearance-none button-base"
            :style="{
                '--btn-bg': Couleurs.main_green,
                '--btn-hover-bg': Couleurs.dark_green
            }"
        >
            Suivant
        </button>
        <button
            v-else
            type="button"
            @click="CreateEvents"
            class="appearance-none button-base"
            :style="{
                '--btn-bg': Couleurs.main_green,
                '--btn-hover-bg': Couleurs.dark_green
            }"
        >
            Dupliquer les évènements
        </button>
    </div>

</template>

<script>
import eventsService from '@/javascript/api/axios_events.js';
import { Events } from "@/javascript/constants/events_type"
import { Couleurs } from "@/javascript/constants/colors"

export default {
    emits: ['cancelSignal'],
    props: {
        event: {
            type: Object,
            required: true
        }
    },

    data() {
        return {
            Events,
            Couleurs,
            errorString: null,
            listEvents: [
                {
                    ...this.emptyCopyEvent()
                }
            ],
            currentStep: 0,
        }
    },

    computed: {
        categorieForm:{
            get() {
                return this.event.categorie
            }
        },
        startDateComp: {
            get() {
                return this.listEvents[this.currentStep].startDate;
            },
            set(value) {
                this.listEvents[this.currentStep].startDate = value;
            }
        },
        endDateComp: {
            get() {
                return this.listEvents[this.currentStep].endDate;
            },
            set(value) {
                this.listEvents[this.currentStep].endDate = value;
            }
        },
        urlBilleterieComp: {
            get() {
                return this.listEvents[this.currentStep].url_billeterie;
            },
            set(value) {
                this.listEvents[this.currentStep].url_billeterie = value;
            }
        },
        attentePlaceComp: {
            get() {
                return this.listEvents[this.currentStep].attentePlace;
            },
            set(value) {
                this.listEvents[this.currentStep].attentePlace = value;
            }
        },

        isCheckedAttente: {
            get() {
                return this.listEvents[this.currentStep].hasAttente;
            },
            set(value) {
                this.listEvents[this.currentStep].hasAttente = value;
                if (!value) {
                    this.listEvents[this.currentStep].attentePlace = 0;
                }
            }
        },

        isChangeValid(){
            const currentEvent = this.listEvents[this.currentStep];
            const hasStartingDate = !!currentEvent.startDate;
            const hasEndingDate = !!currentEvent.endDate;
            const EndingLaterThanStarting = new Date(currentEvent.startDate) < new Date(currentEvent.endDate);
            const endDateValid = hasEndingDate && EndingLaterThanStarting;
            return hasStartingDate && (endDateValid || !hasEndingDate);
        },
        isLastStep() {
            return this.currentStep === this.listEvents.length - 1;
        },
    },

    methods: {
        addRange() {
            this.listEvents.push({
                ...this.emptyCopyEvent(),
                startDate: this.listEvents[this.currentStep].startDate,
                endDate: this.listEvents[this.currentStep].endDate
            });
        },
        removeRange(index) {
            if (index === 0) return;
            this.listEvents.splice(index, 1);
            this.currentStep = Math.max(0, index - 1);
        },
        nextStep() {
            if (this.isChangeValid) {
                this.currentStep++;
                return
            }
            this.errorString = "Veuillez vérifier les champs de dates avant de passer à l'étape suivante.";
        },
        previousStep() {
            if (this.currentStep > 0 && this.isChangeValid) {
                this.currentStep--;
            }
        },
        emptyCopyEvent() {
            return {
                ...this.event,
                startDate: null,
                endDate: null,
                url_billeterie: null,
                attentePlace: this.event.attentePlace,
                hasAttente: this.event.attentePlace > 0,
            }
        },
        Cancel() {
            this.$emit("cancelSignal")
        },
        async CreateEvents() {
            if (!this.isChangeValid) {
                this.errorString = "Veuillez vérifier les champs de dates avant de dupliquer les évènements.";
                return;
            }
            let success = true
            for (const event of this.listEvents) {
                let response_clone = await eventsService.createEvent(event);
                if(response_clone?.data?.id) {
                    event.id = response_clone.data.id
                    event.post_id = response_clone.data.post_id
                    event.users = []
                }
                else {
                    success = false
                    this.errorString = "Une erreur est survenue lors de la duplication de l'évènement. Veuillez réessayer."
                    break
                }
            }
            if (success) {
                this.$emit("cancelSignal")
            }
        }
    }
}

</script>

<style>
.steps-indicator {
    display: flex;
    justify-content: center;
    gap: 10px;
    padding: 12px 0 4px;
}

.step-dot {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 600;
    border: 2px solid #ccc;
    color: #aaa;
    cursor: default;
    transition: all 0.2s;
}

.step-dot.active {
    border-color: #2d5c7f;
    color: #2d5c7f;
    background: #eaf2fa;
}

.step-dot.done {
    border-color: #2d5c7f;
    background: #2d5c7f;
    color: white;
    cursor: pointer;
}

.step-title {
    font-weight: 600;
    font-size: 15px;
    margin: 8px 0 12px;
    color: #333;
}
</style>