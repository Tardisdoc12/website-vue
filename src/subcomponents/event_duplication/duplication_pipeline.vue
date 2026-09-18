<template>
    <div class="steps-indicator">
        <div
            v-for="(p, index) in events"
            :key="index"
            class="step-dot"
            :class="{ done: currentStep === index, active: currentStep > index }"
            @click="currentStep > index ? currentStep = index : null"
        >
            <span>{{ index + 1 }}</span>
        </div>
        <div
            class="step-dot"
            :class="{done: true}"
            @click="addEvent"
        >+</div>
    </div>

    <div v-if="currentStep > 0" style="text-align:right; margin: 0 20px;">
        <button type="button" class="button-remove" @click="removeEvent(currentStep)">
            ✕ Retirer cet événement
        </button>
    </div>

    <div style="border:1px solid #c0c0c0;border-radius: 0%;">
        <EventDuplicationOneEvent
            :key="currentStep"
            v-model:eventSelected="events[currentStep]"
            :placesEvent="placesEvents"
        />
    </div>

    <!-- ───── Actions ───── -->
    <div style="margin: 16px 20px 10px; display:flex; flex-direction:column; gap:8px;">

        <!-- Bouton Étape suivante / Terminer -->
        <div style="display:flex; gap:8px; justify-content:center;">
            <button
                v-if="currentStep > 0"
                type="button"
                class="button-base button-secondary"
                @click="currentStep--"
            >
                ← Retour
            </button>

            <button
                v-if="!isLastStep"
                type="button"
                class="button-base"
                @click="nextStep"
            >
                Suivant →
            </button>

            <button
                v-if="isLastStep"
                type="button"
                class="button-base"
                :disabled="isSubmitting"
                @click="handleSubmit"
            >
                {{ isSubmitting ? 'Dupliquer' : 'Dupliquer l\'événement' }}
            </button>
        </div>

    </div>
</template>

<script>
import EventDuplicationOneEvent from './event_duplication_one_event.vue'

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
    name: 'DuplicationPipeline',

    props: {
        event:  { type: Object, required: true },
        placesEvents: { type: Array, required: true },
        onSuccess: {
            type: Function,
            required: false,
            default: () => {}
        }
    },

    data() {
        return {
            currentStep: 0,
            maxDuplication: 10,
            isSubmitting: false,
            events: [
                {
                    ...this.event,
                    isCheckedSavePlace: false,
                    payementTitle: ''
                }
            ]
        }
    },

    computed: {
        isLastStep() {
            return this.currentStep === this.events.length - 1
        },
    },

    methods: {
        addEvent() {
            this.events.push({
                ...this.event,
                startDate: this.events[this.currentStep].startDate,
                isCheckedSavePlace: false,
                payementTitle: ''
            })
            this.currentStep = this.events.length - 1
        },

        removeEvent(index) {
            this.events.splice(index, 1)
            this.currentStep = Math.min(this.currentStep, this.events.length - 1)
        },


        nextStep() {
            this.currentStep++
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


            if (!event.payementTitle){
                event.payementTitle = event.title + " - " + formatDateFr(event.startDate)
            }
            const response = await eventsService.createEvent(event)
            return response;
        },

        async createOneEvent(event) {
            const places = this.placesEvents.filter(place => place.name === event.place);
            if (!places.length && event.isCheckedSavePlace) {
                
                const response = await placesApi.add_place({ name: event.place });
                if (!response?.data?.success) {
                    console.error("❌ Erreur lors de l'ajout du lieu:", response?.data?.message || "Unknown error");
                }
            }

            const response = await this.createEvent(event)
            if (Number(response?.status) == 200) {
                return true
            }
            return false
        },

        async handleSubmit() {
            this.isSubmitting = true
            try {
                let successCount = 0
                for (const event of this.events) {
                    const success = await this.createOneEvent(event)
                    if (success) successCount++
                }
                console.log(`✅ ${successCount} sur ${this.events.length} événements créés avec succès.`)
                if (this.onSuccess) {
                    this.onSuccess()
                }
            } catch (err) {
                console.error("❌ Erreur inscription:", err)
            } finally {
                this.isSubmitting = false
            }
        }
    },

    components: {
        EventDuplicationOneEvent
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
    border-color: var(--secondary-color);
    color: var(--secondary-color);
    background: #eaf2fa;
}

.step-dot.done {
    border-color: var(--secondary-color);
    background: var(--secondary-color);
    color: var(--writing-main-color);
    cursor: pointer;
}

.step-title {
    font-weight: 600;
    font-size: 15px;
    margin: 8px 0 12px;
    color: #333;
}

.found-card {
    margin-top: 8px;
    padding: 10px 14px;
    background: #edf7ed;
    border: 1px solid #a3d9a5;
    border-radius: 6px;
    color: #2e7d32;
    font-weight: 500;
}

.not-found-msg {
    margin: 8px 0 10px;
    font-size: 13px;
    color: #888;
    font-style: italic;
}

.button-base {
    padding: 8px 20px;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    transition: opacity 0.2s;
}

.button-base:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.button-secondary {
    background: #f0f0f0;
    color: #444;
    border: 1px solid #ccc;
}

.toggle-btn {
    flex: 1;
    padding: 8px;
    border: 2px solid #ccc;
    border-radius: 6px;
    background: none;
    color: #555;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.toggle-btn--active {
    border-color: var(--secondary-color);
    background: #eaf2fa;
    color: var(--secondary-color);
}

.button-remove {
    background: none;
    border: none;
    color: #c0392b;
    font-size: 12px;
    cursor: pointer;
    padding: 2px 0;
    opacity: 0.7;
    transition: opacity 0.2s;
}

.button-remove:hover {
    opacity: 1;
}

.button-add {
    background: none;
    border: 1px dashed #aaa;
    color: #666;
    padding: 6px 16px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 13px;
    transition: all 0.2s;
}

.button-add:hover {
    border-color: var(--secondary-color);
    color: var(--secondary-color);
}

.flex { display: flex; }
.flex-col { flex-direction: column; }
.gap-1 { gap: 6px; margin-bottom: 10px; }
.block { display: block; }
.font-medium { font-weight: 500; }
.w-full { width: 100%; }
.border { border: 1px solid #d1d5db; }
.p-1 { padding: 6px 8px; }
.rounded { border-radius: 4px; }
.items-center { align-items: center; }
.justify-center { justify-content: center; }
</style>