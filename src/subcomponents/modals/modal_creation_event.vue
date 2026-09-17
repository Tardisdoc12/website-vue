<template>
    <Modal
        :title="titleName"
        :width="'60%'"
        @changeBool="HandleCancel"
    >
        <EventCreationPipeline 
            :eventSelected="eventSelected" 
            :placesEvent="placesEvent"
            :billeteries="billeteries" 
            @validate="HandleValidate"
            @cancel="HandleCancel"
        />
    </Modal>
</template>

<script>
import Modal from "@/subcomponents/unitary_elements/modalComponent.vue"
import EventCreationPipeline from "@/subcomponents/event_creation/event_creation_pipeline.vue"

export default {
    name: "ModalCreationEvent",

    signals:[
        'createEvents',
        'cancelSignal'
    ],

    props: {
        onSuccess: {
            type: Function,
            default: null,
        },
        eventSelected: {
            type: Object,
            default: null,
        },
        placesEvent: {
            type: Array,
            default: () => [],
        },
        billeteries: {
            type: Array,
            default: () => [],
        },
    },

    data() {
        return {
            titleName: "Création d'évènement",
        }
    },

    methods: {
        HandleCancel(){
            this.$emit('cancelSignal', !this.isOpen)
        },

        HandleValidate(new_event){
            console.log("New event:", new_event)
            // this.$emit("createEvents", new_event)
        }
    },

    components: {
        Modal,
        EventCreationPipeline
    },
}
</script>