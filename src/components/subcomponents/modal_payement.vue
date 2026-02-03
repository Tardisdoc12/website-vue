<template>
    <Modal
        :title="title"
        :isCancel="isOpen"
        @changeBool="Cancel"
    >
        <p style="margin-bottom: 10px; margin-top: 10px;">{{  "Votre inscription ne sera validée qu'après paiement. Merci de Cliquer sur le Bouton pour procéderr au payement" }}</p>
        <iframe class="center-helloasso" :src="getBilleterieURL" style="width: 80vw; height: 80vh; border: none;">
        </iframe>
    </Modal>
</template>

<script>
import Modal from "./unitary_elements/modalComponent.vue"

export default {
    props: {
        Date: {
            type: String,
            required: true,
        },
    },

    data() {
        return {
            isOpen: false,
            title: "Règlement"
        }
    },

    components:{
        Modal
    },

    computed: {

        getBilleterieURL() {
            if (this.compareDate(this.Date, 1, 22, 2026)) {
                return "https://www.helloasso.com/associations/mps-moto/evenements/stage-reprise-de-guidon/widget-bouton"
            } else if (this.compareDate(this.Date, 2, 8, 2026)) {
                return "https://www.helloasso.com/associations/mps-moto/evenements/stage-reprise-de-guidon-8-mars/widget-bouton"
            } 
            else if (this.compareDate(this.Date, 2, 22, 2026)) {
                return "https://www.helloasso.com/associations/mps-moto/evenements/stage-reprise-de-guidon-22-mars/widget-bouton"
            }
            else {
                return "https://www.helloasso.com/associations/mps-moto/evenements/inscription-seance/widget-bouton"
            }
        }
    },

    methods:{
        Cancel() {
            this.$emit('cancelSignal', !this.isOpen)
        },

        compareDate(eventDate, month, day, year) {
            const eventDateF = new Date(eventDate);
            const targetDate = new Date(year, month, day);

            eventDateF.setHours(0, 0, 0, 0);
            targetDate.setHours(0, 0, 0, 0);
            const isSameDay = eventDateF.getTime() === targetDate.getTime();

            return isSameDay;
        }
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