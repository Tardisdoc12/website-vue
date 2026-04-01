<template>
    <!-- Corps de l'annonce -->
    <div class="body">
        <div v-if="isBureauComp" style="margin-bottom: 10px;">
            <small @click="RemoveEvent">Supprimer l'évènement</small>
        </div>

        <div v-if="isBureauComp" style="margin-bottom: 10px;">
            <small @click="LockEvent">Verrouiller l'évènement</small>
        </div>

        <div v-if="isBureauComp" style="margin-bottom: 10px;">
            <small @click="FullEvent">Rendre l'évènement complet</small>
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

        <p v-if="isEncadrantComp" style="margin-bottom: 10px;">
            <span style="font-weight: bold; text-decoration: underline;">{{ "Adhérents inscrit :" }}</span>
            <span style="padding: 15px">{{ placeAdherents }}</span>
        </p>

        <p v-if="isEncadrantComp" style="margin-bottom: 10px;">
            <span style="font-weight: bold; text-decoration: underline;">{{ "Non Adhérents inscrit :" }}</span>
            <span style="padding: 15px">{{ placeNonAdherents }}</span>
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
                {{ affichageInscribe }}
            </button>
            <!-- Voir la page de l'event
            <a
                v-if="canBeRedirected"
                :href="event.url_post"
                class="appearance-none button-base"
            >
                {{ "Voir la page" }}
            </a> -->
            <!-- visualisation -->
            <button
                v-if="isBureauComp || isEncadrantComp"
                type="button"
                class="appearance-none button-base"
                @click="VisualizeInscrit"
            >
                Voir les inscrits
            </button>

            <!-- events -->
            <button
                v-if="isBureauComp"
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
import { isAdherent, isEncadrant, isBureau } from "@/javascript/constants/roles";

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
        placeAdherents() {
            if(Number(this.event.subscribePlace) < 0 ){
                return `${this.event.nbr_non_adherents}/∞`
            }
            return `${this.event.nbr_non_adherents}/${this.event.subscribePlace}`
        },
        placeNonAdherents() {
            return `${this.event.nbr_adherents}/${this.event.nonsubscribePlace}`
        },
        isBureauComp() {
            return isBureau(this.roles);
        },

        isAdherentComp() {
            return isAdherent(this.roles);
        },

        isEncadrantComp() {
            return isEncadrant(this.roles)
        },

        affichageInscribe(){
            if(this.event.isInscript){
                return "Déjà inscrit"
            }
            else if(Number(this.event.closed_inscription) === 1){
                return "Inscriptions fermées"
            }
            else if(Number(this.event.closed_inscription) === 2){
                return "Évènement complet"
            }
            else if(Number(this.event.closed_inscription) === 3){
                return "Évènement dépassé"
            }
            else{
                return "Inscription"
            }
        },

        disableSubscribe() {
            if (this.event.isInscript) {
                return true
            }
            if(Number(this.event.closed_inscription) !== 0){
                return true
            }
            if (this.isAdherentComp) {
                if (this.event.subscribePlace - this.event.nbr_non_adherents === 0) {
                    return true
                }
                return false
            }
            else {
                if (this.event.nonsubscribePlace - this.event.nbr_adherents<= 0) {
                    return true
                }
                return false
            }
        },

        espaceDate() {
            const invalidDates = [null, undefined, "", "0000-00-00 00:00:00"];
            if (!invalidDates.includes(this.event.endDate) && !isNaN(new Date(this.event.endDate).getTime())) {
                return formatDate(new Date(this.event.startDate)) + " jusqu'à " + formatDate(new Date(this.event.endDate))
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

        async LockEvent() {
            let newStatus = 0
            if(Number(this.event.closed_inscription) != 1){
                newStatus = 1
            }
            else{
                newStatus = 0
            }
            const response = await api.updateEvent(
                this.event.event_id,
                {
                    title: this.event.title,
                    description: this.event.description,
                    startDate: this.event.startDate,
                    endDate: this.event.endDate,
                    place: this.event.place,
                    categorie: this.event.categorie,
                    subscribePlace: this.event.subscribePlace,
                    nonsubscribePlace: this.event.nonsubscribePlace,
                    closed_inscription: newStatus
                })
            this.$emit('updatedEvent')
            this.$emit('cancelSignal')
        },

        async FullEvent(){
            let newStatus = 0
            if(Number(this.event.closed_inscription) != 2){
                newStatus = 2
            }
            else{
                newStatus = 0
            }
            const response = await api.updateEvent(
                this.event.event_id,
                {
                    title: this.event.title,
                    description: this.event.description,
                    startDate: this.event.startDate,
                    endDate: this.event.endDate,
                    place: this.event.place,
                    categorie: this.event.categorie,
                    subscribePlace: this.event.subscribePlace,
                    nonsubscribePlace: this.event.nonsubscribePlace,
                    closed_inscription: newStatus
                })
            this.$emit('updatedEvent')
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