<template>
    <Modal
        :title="Title"
        @changeBool="Cancel"
    >
        <PresentationsEvent
            v-if="steps == 0"
            :event="event"
            :roles="userConnected?.roles"
            @cancelSignal="Cancel"
            @updateEvent="updateEvent"
            @visualizingUsers="visualizeUsers"
            @inscriptWanted="incrementSteps"
            @deletedEvent="deletedEvent"
        />
        <InscriptionEvent
            v-if="steps == 1"
            :event="event"
            :user="userConnected"
            @inscrit="incrementSteps"
        />
        <PayementEvent
            v-if="steps == 2"
            :Date="event.startDate"
        />
        <ModificationEvent
            v-if="steps == 3"
            :event-selected="event"
            @cancelSignal="Cancel"
        />
        <UsersInEvent
            v-if="steps == 4"
            :users-registered="event.users"
            :event_id="Number(event.event_id)"
            @userDeleted="userToDelete"
        />
    </Modal>
</template>

<script>
import Modal from "@/subcomponents/unitary_elements/modalComponent.vue"
import { Events } from "@/javascript/constants/events_type"
import ModificationEvent from "@/subcomponents/event_inscribe/event_formulaire.vue"
import PresentationsEvent from "@/subcomponents/event_inscribe/event_présentation.vue"
import PayementEvent from "@/subcomponents/event_inscribe/event_payement.vue"
import UsersInEvent from "@/subcomponents/event_inscribe/event_users.vue"
import InscriptionEvent from "@/subcomponents/event_inscribe/event_inscription.vue"

export default{
    props:{
        event: {
            type: Object,
            required: true
        },
        userConnected: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            Events,
            isCancel: false,
            steps: 0,
        }
    },

    computed: {
        Title() {
            switch(this.steps){
                case 0:
                    return this.event.title
                case 1:
                    return "Inscription " + this.event.title
                case 2:
                    return "Payement"
                case 3:
                    return "Modification de l'évènement"
                case 4:
                    return "Personnes Inscrites"
            }
        },
    },

    methods: {
        Cancel() {
            this.$emit("cancelSignal", this.isCancel)
        },

        incrementSteps() {
            this.steps += 1
            if(this.steps == 2) {
                this.$emit("inscritEvent", this.event)
                if(this.event.categorie === Events.balade && !this.userConnected.roles.includes("non_adherent")) {
                    this.$emit("cancelSignal", this.isCancel)
                    return
                }
                if (this.event.categorie !== Events.stage) {
                    this.$emit("cancelSignal", this.isCancel)
                    return
                }
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
        },
        
        updateEvent() {
            this.steps = 3
        },
    },

    components: {
        Modal,
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