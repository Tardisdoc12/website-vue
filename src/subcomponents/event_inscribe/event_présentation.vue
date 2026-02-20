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
        <div class="flex items-center justify-center" :style="{ gap: '10px'}">
            <button
                :disabled="disableSubscribe"
                type="button"
                class="appearance-none button-base"
                :style="{
                    '--btn-color': Couleurs.white,
                    '--btn-bg': disableSubscribe ? Couleurs.gris_pale : Couleurs.vert,
                    '--btn-hover-bg': disableSubscribe ? Couleurs.gris_pale : Couleurs.dark_vert
                }"
                @click="Register"
            >
                {{ this.event.isInscript ? "Déjà Inscrit" : "Inscription" }}
            </button>
            <!-- Voir la page de l'event -->
            <a
                v-if="canBeRedirected"
                :href="event.url_post"
                class="appearance-none button-base"
            >
                {{ "Voir la page" }}
            </a>
            <!-- visualisation -->
            <button
                v-if="isBureau || isEncadrant"
                type="button"
                class="appearance-none button-base"
                @click="VisualizeInscrit"
            >
                Voir les inscrits
            </button>

            <!-- events -->
            <button
                v-if="isBureau"
                type="button"
                class="appearance-none button-base"
                @click="updateEvent"
            >
                Modifier l'évènement
            </button>
        </div>
    </div>
</template>

<script>
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
        },

        canBeRedirected: {
            type: Boolean,
            required: false,
            default: false,
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
            const response = await api.deleteEvent(this.event.event_id)
            this.$emit('deletedEvent', this.event.event_id)
            this.$emit('cancelSignal')
        },

        Register() {
            this.$emit('inscriptWanted')
        }
    }
}
</script>

<style>
.body {
    padding: 1rem;
}
</style>