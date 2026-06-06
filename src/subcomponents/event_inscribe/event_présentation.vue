<template>
    <!-- Corps de l'annonce -->
    <div class="body">
        <div
            v-if="isBureauComp"
            class="flex items-center justify-center"
            :style="{
                gap: '10px',
                'margin-bottom': '10px',
            }"
        >
            <button
                type="button"
                :style="{
                    '--btn-bg': Couleurs.main_red,
                    '--btn-hover-bg': Couleurs.dark_red
                }"
                @click="RemoveEvent"
                class="appearance-none button-base"
            >
                <font-awesome-icon icon="fa-solid fa-trash" />
            </button>
            
            <button
                type="button"
                :style="{
                    '--btn-bg': Number(event.closed_inscription) === 1 ? Couleurs.main_red : Couleurs.main_blue,
                    '--btn-hover-bg': Number(event.closed_inscription) === 1 ? Couleurs.dark_red : Couleurs.dark_blue
                }"
                @click="LockEvent"
                class="appearance-none button-base"
            >
                <font-awesome-icon
                    v-if="Number(event.closed_inscription) !== 1"
                    icon="fa-solid fa-lock-open"
                />
                <font-awesome-icon
                    v-else
                    icon="fa-solid fa-lock"
                />
            </button>

            <div>
                <small @click="FullEvent">Rendre l'évènement complet</small>
            </div>

            <button
                type="button"
                :style="{
                    '--btn-bg': Couleurs.main_blue,
                    '--btn-hover-bg': Couleurs.dark_blue
                }"
                @click="ajoutPerson"
                class="appearance-none button-base"
            >
                <font-awesome-icon icon="fa-solid fa-user-plus"/>
            </button>

            <button
                type="button"
                :style="{
                    '--btn-bg': Couleurs.main_blue,
                    '--btn-hover-bg': Couleurs.dark_blue
                }"
                @click="CopyEvent"
                class="appearance-none button-base"
            >
                <font-awesome-icon icon="fa-solid fa-clone"/>
            </button>
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
                    '--btn-bg': colorButton,
                    '--btn-hover-bg': colorHoverButton
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
                <font-awesome-icon icon="fa-solid fa-clipboard-list" />
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
            isAttente: this.event.attentePlace - this.event.nbr_attente > 0,
        }
    },

    computed: {
        placeAdherents() {
            if(Number(this.event.subscribePlace) < 0 ){
                return `${this.event.nbr_adherents}/∞`
            }
            return `${this.event.nbr_adherents}/${this.event.subscribePlace}`
        },
        placeNonAdherents() {
            return `${this.event.nbr_non_adherents}/${this.event.nonsubscribePlace}`
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
            const isFullAdherent = this.event.subscribePlace - (this.event.nbr_adherents) <= 0
            const isFullNonAdherent = this.event.nonsubscribePlace - (this.event.nbr_non_adherents) <= 0
            const isAdherent = this.isAdherentComp
            let isOkay = false
            if(isAdherent){
                isOkay = isFullAdherent
            }
            else{
                isOkay = isFullNonAdherent
            }
            if(this.event.isInscript){
                return "Se désinscrire"
            }
            else if(Number(this.event.closed_inscription) === 1){
                return "Inscriptions fermées"
            }
            else if(Number(this.event.closed_inscription) === 2 && (!this.isAttente)){
                return "Évènement complet"
            }
            else if(Number(this.event.closed_inscription) === 3){
                return "Évènement dépassé"
            }
            else if (this.isAttente && isOkay){
                return "Inscription (liste d'attente)"
            }
            else{
                return "Inscription"
            }
        },

        colorButton() {
             if(this.event.isInscript){
                return Couleurs.main_red
             }
             else if(Number(this.event.closed_inscription) === 1){
                return Couleurs.gris_pale
             }
             else if(Number(this.event.closed_inscription) === 2 && (!this.isAttente)){
                return Couleurs.gris_pale
             }
             else if(Number(this.event.closed_inscription) === 3){
                return Couleurs.gris_pale
             }
             else if (this.isAttente){
                return Couleurs.vert
             }
             else{
                return Couleurs.vert
             }
        },

        colorHoverButton() {
            if(this.event.isInscript){
                return Couleurs.dark_red
            }
            else if(Number(this.event.closed_inscription) === 1){
                return Couleurs.gris_pale
            }
            else if(Number(this.event.closed_inscription) === 2 && (!this.isAttente)){
                return Couleurs.gris_pale
            }
            else if(Number(this.event.closed_inscription) === 3){
                return Couleurs.gris_pale
            }
            else if (this.isAttente){
                return Couleurs.dark_vert
            }
            else{
                return Couleurs.dark_vert
            }
        },

        disableSubscribe() {
            if (this.event.isInscript) {
                return false
            }
            if(Number(this.event.closed_inscription) !== 0){
                return true
            }
            if (this.isAdherentComp && this.event.attentePlace <= 0) {
                if (this.event.subscribePlace - this.event.nbr_adherents === 0) {
                    return true
                }
                return false
            }
            else if(this.event.attentePlace > 0) {
                if (!this.isAttente) {
                    return true
                }
                return false
            }
            else {
                if (this.event.nonsubscribePlace - this.event.nbr_non_adherents<= 0) {
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
                    attentePlace: this.event.attentePlace,
                    closed_inscription: newStatus
                })
            this.$emit('updatedEvent')
            this.$emit('cancelSignal')
        },

        ajoutPerson() {
            this.$emit('addPerson')
        },

        CopyEvent() {
            this.$emit('copyEvent')
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
                    attentePlace: this.event.attentePlace,
                    closed_inscription: newStatus
                })
            this.$emit('updatedEvent')
            this.$emit('cancelSignal')
        },

        Register() {
            if(this.event.isInscript){
                this.$emit('uninscriptEvent')
                return
            }
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