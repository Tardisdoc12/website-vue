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
        @copyEvent="CopyEvent"
        @deletedEvent="deletedEvent"
        @addPerson="addUser"
    />
    <InscriptionEvent
        v-if="stepsComputed == 1"
        :event="event"
        :user="userConnected"
        :isAttente="isAttenteComp"
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
    <EncadrantAddPerson
        v-if="stepsComputed == 5"
        :event="event"
        @inscrit="() => {this.$emit('cancelSignal', this.isCancel)}"
    />
    <DuplicationEvent
        v-if="stepsComputed == 6"
        :event="event"
        @cancelSignal="Cancel"
    />
</template>

<script>
import { Events } from "@/javascript/constants/events_type"
import ModificationEvent from "@/subcomponents/event_inscribe/event_formulaire.vue"
import PresentationsEvent from "@/subcomponents/event_inscribe/event_présentation.vue"
import PayementEvent from "@/subcomponents/event_inscribe/event_payement.vue"
import UsersInEvent from "@/subcomponents/event_inscribe/event_users.vue"
import InscriptionEvent from "@/subcomponents/event_inscribe/event_inscription.vue"
import EncadrantAddPerson from "@/subcomponents/event_inscribe/event_encadrant_add_person.vue"
import DuplicationEvent from "@/subcomponents/event_inscribe/event_duplication.vue"

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
            listMembers: null,
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
        },

        isAttenteComp() {
            if (this.event.attentePlace - this.event.nbr_attente > 0) {
                return true
            }

            const isListAttente = this.event.attentePlace > 0
            const isFullAdherent = this.event.subscribePlace - (this.event.nbr_non_adherents) <= 0
            const isFullNonAdherent = this.event.nonsubscribePlace - (this.event.nbr_adherents) <= 0
            const isAdherent = this.userConnected.roles?.includes("adherent")
            const isFull = isAdherent ? isFullAdherent : isFullNonAdherent
            if(isFull && isListAttente){
                return true
            }
            return false
        }
    },

    methods: {
        Cancel() {
            this.$emit("cancelSignal", this.isCancel)
        },

        onInscrit(participants) {
            this.lastParticipants = participants
            let isFulladherent = this.event.subscribePlace - (this.event.nbr_non_adherents) <= 0
            let isFullNonAdherent = this.event.nonsubscribePlace - (this.event.nbr_adherents) <= 0
            for (const participant of participants) {
                if (participant.roles?.includes("adherent") ) {
                    if(isFulladherent){
                        participant.status = "attente"
                        this.event.nbr_attente += 1
                        if(this.event.attentePlace - this.event.nbr_attente < 0){
                            this.participants = this.participants.filter(p => p.id !== participant.id)
                            this.event.nbr_attente -= 1
                        }
                        break
                    }
                    participant.status = "inscrit"
                    isFulladherent = this.event.subscribePlace - (this.event.nbr_non_adherents + 1) <= 0
                }
                else {
                    if(isFullNonAdherent){
                        participant.status = "attente"
                        this.event.nbr_attente += 1
                        if(this.event.attentePlace - this.event.nbr_attente < 0){
                            this.participants = this.participants.filter(p => p.id !== participant.id)
                            this.event.nbr_attente -= 1
                        }
                        break
                    }
                    participant.status = "inscrit"
                    isFullNonAdherent = this.event.nonsubscribePlace - (this.event.nbr_adherents + 1) <= 0
                }
            }
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
        
        addUser() {
            this.steps = 5
            this.$emit("incrementSteps", this.steps)
        },

        CopyEvent() {
            this.steps = 6
            this.$emit("incrementSteps", this.steps)
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
        EncadrantAddPerson,
        DuplicationEvent
    }
}
</script>

<style>
</style>