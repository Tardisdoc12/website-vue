<template>
    <!-- Corps de l'annonce -->
    <div class="body">
        <div v-if="isBureau" style="margin-bottom: 10px;">
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
            <span style="padding: 15px">{{ event.place }}</span>
        </p>

        <!-- Description -->
            <div style="margin-bottom: 10px;">
            <span style="font-weight: bold; text-decoration: underline;">{{ "Description :"}}</span>
            <p
                style="white-space: pre-line;"
            >
                {{ event.description }}
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
                    backgroundColor: disableSubscribe ? Couleurs.gris_pale : Couleurs.vert
                }"
                @click="Register"
            >
                {{ this.event.isInscript ? "Déjà Inscrit" : "Inscription" }}
            </button>

            <!-- visualisation -->
            <button
                v-if="isBureau || isEncadrant"
                type="button"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-600 ml-5"
                :style="{
                    display: inline-block,
                    color: Couleurs.white,
                    padding: '0.5rem 1rem',
                    borderRadius: '0.375rem',
                    backgroundColor: Couleurs.main_blue
                }"
                @click="VisualizeInscrit"
                style="margin-left: 20px;"
            >
                Voir les inscrits
            </button>

            <!-- events -->
            <button
                v-if="isBureau"
                type="button"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-600 ml-5"
                :style="{
                    display: inline-block,
                    color: Couleurs.white,
                    padding: '0.5rem 1rem',
                    borderRadius: '0.375rem',
                    backgroundColor: Couleurs.main_blue
                }"
                @click="updateEvent"
                style="margin-left: 20px;"
            >
                Modifier l'évènement
            </button>
        </div>
    </div>
</template>

<script>

import Modal from "@/subcomponents/unitary_elements/modalComponent.vue"
import ModalInscrit from "@/subcomponents/modals/modal_inscrits.vue"
import ModalEvent from "@/subcomponents/modals/modal_formulaire_events.vue"
import { Couleurs } from "@/javascript/constants/colors"
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
        event: {
            type: Object,
            required: true,
        },

        roles: {
            type: Array,
            required: false,
            default: ["non_adherent"]
        }
    },

    data() {
        return {
            Couleurs,
        }
    },

    computed: {
        isBureau() {
            const listA = ['bureau', 'administrator']
            return this.roles.some(el => listA.includes(el));
        },

        isAdherent() {
            return !this.roles.includes("non_adherent")
        },

        isEncadrant() {
            return this.roles.includes("encadrant")
        },

        disableSubscribe() {
            if (this.event.isInscript) {
                return true
            }
            if (this.isAdherent) {
                if (this.event.subscribePlace === 0) {
                    return true
                }
                return false
            }
            else {
                if (this.event.nonsubscribePlace <= 0) {
                    return true
                }
                return false
            }
        },

        espaceDate() {
            const invalidDates = [null, undefined, "", "0000-00-00 00:00:00"];
            if (!invalidDates.includes(this.event.endDate) && !isNaN(new Date(this.event.endDate).getTime())) {
                return eventatDate(new Date(this.event.startDate)) + " jusqu'à " + formatDate(new Date(this.event.endDate))
            } else {
                return formatDate(new Date(this.event.startDate))
            }
            
        }
    },

    methods: {
        updateEvent() {
            this.$emit('updateEvent')
        },

        VisualizeInscrit() {
            this.$emit('visualizingUsers')
        },

        async RemoveEvent() {
            this.$emit('cancelSignal')
            const response = await api.deleteEvent(this.event.event_id)
        },

        Register() {
            this.$emit('inscriptWanted')
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