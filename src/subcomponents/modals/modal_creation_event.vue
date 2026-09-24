<template>
    <Modal
        :title="titleName"
        :width="isModalMobile"
        @changeBool="HandleCancel"
    >
        <EventCreationPipeline
            :eventSelected="eventSelected" 
            :placesEvent="placesEvent" 
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
    },

    mounted(){
        this.mediaQuery = window.matchMedia("(max-width: 768px)")
        this.isMobile = this.mediaQuery.matches
        this.mediaQuery.addEventListener("change", this.onChange)
    },

    beforeUnmount() {
        this.mediaQuery.removeEventListener("change", this.onChange)
    },


    data() {
        return {
            titleName: "Création d'évènement",
            isMobile: false,
            mediaQuery: null,
        }
    },

    computed: {
        isModalMobile() {
            return this.isMobile ? '100%' : '60%'
        }
    },

    methods: {
        onChange(event) {
            this.isMobile = event.matches
        },
        HandleCancel(){
            this.$emit('cancelSignal', !this.isOpen)
        },

        HandleValidate(new_event){
            this.$emit("createEvents", new_event)
        }
    },

    components: {
        Modal,
        EventCreationPipeline
    },
}
</script>