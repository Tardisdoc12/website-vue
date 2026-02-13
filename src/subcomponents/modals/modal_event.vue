<template>
    <Modal
        v-if="!visualiseInscrit"
        :title="form.title"
        :isCancel="isOpen"
        @changeBool="Cancel"
    >
        <!-- Corps de l'annonce -->
        <div class="body">
            <div v-if="showDeleteButton" style="margin-bottom: 10px;">
                <small @click="RemoveEvent">Supprimer l'évènement</small>
            </div>
            <!-- Dates -->
             <div style="margin-bottom: 10px;">
                <span style="font-weight: bold; text-decoration: underline;">{{ "Date :"}}</span>
                <span style="padding: 15px">{{ espaceDate }}</span>
            </div>
            <!-- Place -->
            <p style="margin-bottom: 10px;">
                <span style="font-weight: bold; text-decoration: underline;">{{ "Lieu :" }}</span>
                <span style="padding: 15px">{{ form.place }}</span>
            </p>

            <!-- Description -->
             <div style="margin-bottom: 10px;">
                <span style="font-weight: bold; text-decoration: underline;">{{ "Description :"}}</span>
                <p
                    style="white-space: pre-line;"
                >
                    {{ form.description }}
                </p>
            </div>
            
            <!-- bouton -->
            <div class="flex items-center justify-center">
                <button
                    :disabled="disableSubscribe"
                    type="button"
                    class="appearance-none focus:outline-none px-4 py-2 rounded 
                    text-white 
                    bg-green-500 hover:bg-green-600 
                    disabled:bg-gray-400 disabled:cursor-not-allowed"
                    :style="{
                        display: inline-block,
                        color: white,
                        padding: '0.5rem 1rem',
                        borderRadius: '0.375rem',
                        backgroundColor: disableSubscribe ? '#9ca3af' : '#22c55e'
                    }"
                    @click="Register"
                >
                    {{ this.form.isInscript ? "Déjà Inscrit" : "Inscription" }}
                </button>

                <!-- visualisation -->
                <button
                    v-if="showDeleteButton || isEncadrant"
                    type="button"
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-600 ml-5"
                    :style="{
                        display: inline-block,
                        color: white,
                        padding: '0.5rem 1rem',
                        borderRadius: '0.375rem',
                        backgroundColor: '#2563EB'
                    }"
                    @click="VisualizeInscrit"
                    style="margin-left: 20px;"
                >
                    Voir les inscrits
                </button>

                <!-- events -->
                <button
                    v-if="showDeleteButton"
                    type="button"
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-600 ml-5"
                    :style="{
                        display: inline-block,
                        color: white,
                        padding: '0.5rem 1rem',
                        borderRadius: '0.375rem',
                        backgroundColor: '#2563EB'
                    }"
                    @click="updateEvent"
                    style="margin-left: 20px;"
                >
                    Modifier l'évènement
                </button>
            </div>
        </div>
    </Modal>

    <ModalInscrit
        v-if="visualiseInscrit"
        :userRegister="form.users"
        :event_id="form.event_id"
        @cancelSignal="Cancel"
    />

    <ModalEvent
        v-if="toUpdateEvent"
        :eventSelected="form"
        @cancelSignal="CancelUpdate"
    />
</template>

<script>

import Modal from "@/subcomponents/unitary_elements/modalComponent.vue"
import ModalInscrit from "@/subcomponents/modals/modal_inscrits.vue"
import ModalEvent from "@/subcomponents/modals/modal_formulaire_events.vue"
import api from "@/javascript/api/axios_events"

function formatDate(d) {
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    const hours = String(d.getHours()).padStart(2, '0');
    const minutes = String(d.getMinutes()).padStart(2, '0');
    return `le ${day}/${month}/${year} à ${hours}:${minutes}`;
}

export default {

    props: {
        placeSubscribe: {
            type: Number,
            required: true
        },

        placeNonSubscribe: {
            type: Number,
            required: true
        },

        showDeleteButton: {
            type: Boolean,
            required: true,
        },

        form: {
            type: Object,
            required: true,
        },

        roles: {
            type: Array,
            required: false
        }
    },

    data() {
        return {
            isWantedInscript: false,
            isOpen: true,
            visualiseInscrit: false,
            toUpdateEvent: false,
        }
    },

    computed: {
        isAdherent() {
            if (this.roles) {
                return !this.roles.includes("non_adherent")
            }
            return false
        },

        isEncadrant() {
            if(this.roles) {
                return this.roles.includes("encadrant")
            }
            return false
        },

        disableSubscribe() {
            if (this.form.isInscript) {
                return true
            }
            if (this.isAdherent) {
                if (this.form.subscribePlace === 0) {
                    return true
                }
                return false
            }
            else {
                if (this.form.nonsubscribePlace <= 0) {
                    return true
                }
                return false
            }
        },

        espaceDate() {
            const invalidDates = [null, undefined, "", "0000-00-00 00:00:00"];
            if (!invalidDates.includes(this.form.endDate) && !isNaN(new Date(this.form.endDate).getTime())) {
                return formatDate(new Date(this.form.startDate)) + " jusqu'à " + formatDate(new Date(this.form.endDate))
            } else {
                return formatDate(new Date(this.form.startDate))
            }
            
        }
    },

    methods: {
        updateEvent() {
            this.toUpdateEvent = true
        },

        VisualizeInscrit() {
            this.visualiseInscrit = true
        },

        async RemoveEvent() {
            this.$emit('cancelSignal', !this.isOpen)
            const response = await api.deleteEvent(this.form.event_id)
        },

        Cancel() {
            this.$emit('cancelSignal', !this.isOpen)
        },

        CancelUpdate(e) {
            this.$emit('cancelSignal', !this.isOpen)
        },

        Register() {
            this.$emit('inscriptWanted', !this.isWantedInscript)
        }
    },

    components: {
        Modal,
        ModalInscrit,
        ModalEvent
    }
}
</script>

<style>
.encadre {
    background-color: rgba(75, 76, 78, 0.84); 
    color: rgb(255, 255, 255);
    padding: 0.5rem 1rem;
    position: relative;  
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
    text-align: center;
}

.encadre .btn-close {
  position: absolute;
  right: 10px; /* colle à droite */
  background: none;
  background-color: rgba(192, 23, 23, 0.8);
  border: none;
  color: white;
  cursor: pointer;
}

.encadre .btn-close:hover {
  background-color: rgba(255, 0, 0, 0.8); /* couleur au survol */
}

.modal {
    position: fixed;
    inset: 0px;

    z-index: 2;
    background-color: rgba(0, 0, 0, 0.5);

    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    flex-basis: 600px;
    background: #fff;
    border-radius: 8px;
    padding: 0;
    overflow: hidden;  
}

.body {
    padding: 1rem;
}
</style>