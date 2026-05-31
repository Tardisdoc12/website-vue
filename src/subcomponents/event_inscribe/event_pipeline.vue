<template>
    <PresentationsEvent
            v-if="stepsComputed == 0"
            :event="event"
            :roles="userConnected?.roles"
            :can-be-redirected="canBeRedirectedComputed"
            @cancelSignal="Cancel"
            @updateEvent="updateEvent"
            @visualizingUsers="visualizeUsers"
            @inscriptWanted="incrementSteps"
            @deletedEvent="deletedEvent"
        />
        <InscriptionEvent
            v-if="stepsComputed == 1"
            :event="event"
            :user="userConnected"
            @inscrit="onInscrit"
        />
        <PayementEvent
            v-if="stepsComputed == 2"
            :Date="event.startDate"
            :billeterie_url="event.billeterie_url"
        />
        <ModificationEvent
            v-if="stepsComputed == 3"
            :event-selected="event"
            @cancelSignal="Cancel"
        />
        <UsersInEvent
            v-if="stepsComputed == 4"
            :users-registered="event.users"
            :event_id="Number(event.event_id)"
            @userDeleted="userToDelete"
        />
</template>

<script>
import { Events } from "@/javascript/constants/events_type"
import ModificationEvent from "@/subcomponents/event_inscribe/event_formulaire.vue"
import PresentationsEvent from "@/subcomponents/event_inscribe/event_présentation.vue"
import PayementEvent from "@/subcomponents/event_inscribe/event_payement.vue"
import UsersInEvent from "@/subcomponents/event_inscribe/event_users.vue"
import InscriptionEvent from "@/subcomponents/event_inscribe/event_inscription.vue"


export default{
    emits: [
        'incrementSteps',
        'inscritEvent',
        'cancelSignal',
        'userDeleted',
        'deletedEvent'
    ],
    props:{
        event: {
            type: Object,
            required: true
        },
        userConnected: {
            type: Object,
            required: true
        },
        stepsToStart:{
            type: Number,
            required: false,
            default: null
        },
        canBeRedirected: {
            type:Boolean,
            required:false,
            default:true
        }
    },
    data() {
        return {
            Events,
            steps: 0,
            lastParticipants: [],
        }
    },

    computed:{
        canBeRedirectedComputed(){
            if(!this.canBeRedirected){
                return this.canBeRedirected
            }
            if(this.event.url_post === false){
                return false
            }
            return true
        },

        stepsComputed() {
            if(this?.stepsToStart !== null){
                this.steps = this.stepsToStart
                return this.stepsToStart
            }
            return this.steps
        }
    },

    methods: {
        Cancel() {
            this.$emit("cancelSignal", this.isCancel)
        },

        onInscrit(participants) {
            this.lastParticipants = participants
            this.incrementSteps()
        },

        incrementSteps() {
            this.steps += 1
            this.$emit("incrementSteps", this.steps)

            if (this.steps == 2) {
                this.$emit("inscritEvent", this.event)

                const categorie = this.event.categorie

                const hasNonAdherent = this.lastParticipants.some(p =>
                    p.roles?.includes("non_adherent")
                )

                // Stage → toujours paiement
                if (categorie === Events.stage) return

                // Séance → paiement si au moins un non-adhérent parmi les inscrits
                if (categorie === Events.seance && hasNonAdherent) return

                // Tous les autres cas → ferme
                this.$emit("cancelSignal", this.isCancel)
            }
        },
        
        deletedEvent(event_id){
            this.$emit('deletedEvent', event_id)
        },

        userToDelete(user) {
            this.$emit("userDeleted", user)
        },

        visualizeUsers() {
            this.steps = 4
            this.$emit("incrementSteps", this.steps)
        },
        
        updateEvent() {
            this.steps = 3
            this.$emit("incrementSteps", this.steps)
        },
    },

    components: {
        ModificationEvent,
        PresentationsEvent,
        PayementEvent,
        UsersInEvent,
        InscriptionEvent,
    }
}
</script>

<style>
</style>