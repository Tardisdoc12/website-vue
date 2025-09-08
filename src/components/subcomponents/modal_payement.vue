<template>
    <Modal
        :title="title"
        :isCancel="isOpen"
        @changeBool="Cancel"
    >
        <p style="margin-bottom: 10px; margin-top: 10px;">{{  "Votre inscription ne sera validé qu'après payement" }}</p>
        <iframe src="https://www.helloasso.com/associations/mps-moto/evenements/inscription-seance/widget" style="width: 100%; height: 100%;">
        </iframe>
    </Modal>
</template>

<script>
import Modal from "./unitary_elements/modalComponent.vue"

export default {
    data() {
        return {
            isOpen: false,
            title: "Règlement"
        }
    },

    components:{
        Modal
    },
    methods:{
        Cancel() {
            this.$emit('cancelSignal', !this.isOpen)
        },
    }
}

</script>

<style>
</style>