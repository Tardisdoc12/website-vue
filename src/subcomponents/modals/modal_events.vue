<template>
    <Modal
        :title="Title"
        @changeBool="Cancel"
    >
        <EventPipeline
            :event="event"
            :user-connected="userConnected"
            @incrementSteps="incrementSteps"
            @inscritEvent="inscritEvent"
            @cancelSignal="Cancel"
            @userDeleted="userToDelete"
            @deletedEvent="deletedEvent"
        />
    </Modal>
</template>

<script>
import Modal from "@/subcomponents/unitary_elements/modalComponent.vue"
import EventPipeline from "@/subcomponents/event_inscribe/event_pipeline.vue"

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
            steps: 0,
        }
    },

    computed: {
        stepsComputed(){
            return this.steps
        },
        Title() {
            switch(this.stepsComputed){
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

        incrementSteps(steps) {
            this.steps = steps
        },
        
        inscritEvent(event){
            this.$emit("inscritEvent", event)
        },

        deletedEvent(event_id){
            this.$emit('deletedEvent', event_id)
        },

        userToDelete(user) {
            this.$emit("userDeleted", user)
        }
    },

    components: {
        Modal,
        EventPipeline,
    }
}
</script>

<style>
</style>