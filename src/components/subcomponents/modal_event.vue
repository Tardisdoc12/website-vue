<template>
    <Modal
        v-if="!visualiseInscrit"
        :title="form.title"
        :isCancel="isOpen"
        @changeBool="Cancel"
    >
        <!-- Corps de l'annonce -->
        <div class="body">
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

            <!-- Adherent Slots -->
            <p style="margin-bottom: 10px;" v-if="isAdherent">
                <span style="font-weight: bold; text-decoration: underline;">{{ "Nombre de place :" }}</span>
                <span style="padding: 15px" v-if="form.subscribePlace > 0">{{ form.subscribePlace }}</span>
                <span style="padding: 15px" v-if="!(form.subscribePlace > 0)">{{ "illimté" }}</span>
            </p>

            <!-- Non Adherent Slots -->
            <p  style="margin-bottom: 10px;" v-else>
                <span style="font-weight: bold; text-decoration: underline;">{{ "Nombre de place :" }}</span>
                <span style="padding: 15px">{{ form.nonsubscribePlace }}</span>
            </p>

            <!-- Description -->
             <div style="margin-bottom: 10px;">
                <span style="font-weight: bold; text-decoration: underline;">{{ "Description :"}}</span>
                <p>{{ form.description }}</p>
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
                    Inscription
                </button>

                <!-- Suppression de l'event -->
                <button
                    v-if="showDeleteButton"
                    type="button"
                    class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 ml-5"
                    @click="RemoveEvent"
                    style="margin-left: 20px;"
                >
                    Supprimer l'event
                </button>

                <!-- visualisation -->
                <button
                    v-if="showDeleteButton"
                    type="button"
                    class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 ml-5"
                    @click="VisualizeInscrit"
                    style="margin-left: 20px;"
                >
                    Voir les inscrits
                </button>
            </div>
        </div>
    </Modal>

    <ModalInscrit
        v-if="visualiseInscrit"
        :userRegister="form.users"
        @cancelSignal="Cancel"
    />
</template>

<script>

import Modal from "./unitary_elements/modalComponent.vue"
import ModalInscrit from "./modal_inscrits.vue"
import api from "@/javascript/axios_events"

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
        }
    },

    mounted() {
        console.log(this.form)
    },

    computed: {
        isAdherent() {
            if (this.roles) {
                return !this.roles.includes("non_adherent")
            }
            return false
        },

        disableSubscribe() {
            if (this.roles) {
                if (!this.roles.includes("non_adherent") && (this.form.subscribePlace != 0))
                {
                    return false
                }
                else if (this.roles.includes("non_adherent") && (this.form.nonsubscribePlace > 0))
                {
                    return false
                }
                return true
            }
            if (this.form.nonsubscribePlace > 0) {
                return false
            }
            return true
        },

        espaceDate() {
            if (this.form.endDate) {
                return formatDate(new Date(this.form.startDate)) + " jusqu'à " + formatDate(new Date(this.form.endDate))
            } else {
                return formatDate(new Date(this.form.startDate))
            }
            
        }
    },

    methods: {
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

        Register() {
            this.$emit('inscriptWanted', !this.isWantedInscript)
        }
    },

    components: {
        Modal,
        ModalInscrit
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