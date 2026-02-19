<template>
    <Modal
        :title="titleName"
        @changeBool="Cancel"
    >
        <CreateEventForm
            :onSuccess="onSuccess"
            :eventSelected="eventSelected"
            @createEvents="EventCreated"
        />
    </Modal>
</template>

<script>
import Modal from "@/subcomponents/unitary_elements/modalComponent.vue"
import CreateEventForm from "@/subcomponents/event_inscribe/event_formulaire.vue"
export default {
    props: {
        onSuccess: {
            type: Function,
            default: null,
        },
        eventSelected: {
            type: Object,
            default: null,
        }
    },

    data() {
        return {
            isOpen: true,
            titleName: "Création d'évènement",
        }
    },

    methods:{
        EventCreated(new_event) {
            this.$emit("createEvents", new_event)
        },

        Cancel(){
            this.$emit('cancelSignal', !this.isOpen)
        }
    },

    components: {
        Modal,
        CreateEventForm
    }
}
</script>

<style>

</style>
