<template>
    <Modal
        :title="form.title"
        :isCancel="isOpen"
        @changeBool="Cancel"
    >
        <!-- Corps de l'annonce -->
        <div class="body">
            <!-- Dates -->
             <div style="margin-bottom: 10px;">
                <p style="font-weight: bold; text-decoration: underline;">{{ "Date :"}}</p>
                <p>{{ espaceDate }}</p>
            </div>
            <!-- Place -->
            <p style="margin-bottom: 10px;">
                <span style="font-weight: bold; text-decoration: underline;">{{ "Lieu :" }}</span>
                <span style="padding: 15px">{{ form.place }}</span>
            </p>

            <!-- Adherent Slots -->
            <p style="margin-bottom: 10px;">
                <span style="font-weight: bold; text-decoration: underline;">{{ "Nombre de place pour les adhérents :" }}</span>
                <span style="padding: 15px" v-if="form.subscribePlace > 0">{{ form.subscribePlace }}</span>
                <span style="padding: 15px" v-if="!(form.subscribePlace > 0)">{{ "illimté" }}</span>
            </p>

            <!-- Non Adherent Slots -->
            <p  style="margin-bottom: 10px;">
                <span style="font-weight: bold; text-decoration: underline;">{{ "Nombre de place pour les non adhérents :" }}</span>
                <span style="padding: 15px">{{ form.nonsubscribePlace }}</span>
            </p>

            <!-- Description -->
             <div style="margin-bottom: 10px;">
                <h2 style="font-weight: bold; text-decoration: underline;">{{ "Description :"}}</h2>
                <p>{{ form.description }}</p>
            </div>
            <!-- bouton -->
            <div class="flex items-center justify-center">
                <button
                    type="button"
                    class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600"
                    @click="Register"
                >
                    Inscription
                </button>
            </div>
        </div>
    </Modal>
</template>

<script>

import Modal from "./unitary_elements/modalComponent.vue"

function formatDate(d) {
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    const hours = String(d.getHours()).padStart(2, '0');
    const minutes = String(d.getMinutes()).padStart(2, '0');
    return `${hours}:${minutes} le ${day}/${month}/${year}`;
}

export default {

    props: {
        form: {
            type: Object,
            required: true,
        }
    },

    data() {
        return {
            isWantedInscript: false,
            isOpen: true,
        }
    },

    computed: {
        espaceDate() {
            return formatDate(new Date(this.form.startDate)) + " à " + formatDate(new Date(this.form.endDate))
        }
    },

    methods: {
        Cancel() {
            this.$emit('cancelSignal', !this.isOpen)
        },

        Register() {
            this.$emit('inscriptWanted', !this.isWantedInscript)
        }
    },

    components: {
        Modal
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