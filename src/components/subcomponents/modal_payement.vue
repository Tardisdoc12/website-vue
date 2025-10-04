<template>
    <Modal
        :title="title"
        :isCancel="isOpen"
        @changeBool="Cancel"
    >
        <p style="margin-bottom: 10px; margin-top: 10px;">{{  "Votre inscription ne sera validée qu'après paiement. Merci de Cliquer sur le Bouton pour procéderr au payement" }}</p>
        <iframe class="center-helloasso" src="https://www.helloasso.com/associations/mps-moto/evenements/inscription-seance/widget-bouton" style="width: 80vw; height: 80vh; border: none;">
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
.center-helloasso {
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>