<template>
    <Modal
        :title="Title"
        :width="'80%'"
        @changeBool="Cancel"
    >
        <PresentationsEvent
            :event="event"
            @uninscriptEvent="DeleteUser"
        />
    </Modal>
</template>

<script>
import Modal from "@/subcomponents/unitary_elements/modalComponent.vue"
import PresentationsEvent from "@/subcomponents/event_inscribe/event_présentation.vue"
import api from "@/javascript/api/axios_inscription"

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
            isCancel: false,
        }
    },

    computed: {
        Title() {
            return this.event.title 
        },
    },

    methods: {
        Cancel() {
            this.$emit("cancelSignal", this.isCancel)
        },
       
        async DeleteUser() {
            const tempEventUserList = this.event.users.filter(user => user.email === this.userConnected.email)
            const response = await api.delete_inscrit(this.event.id, tempEventUserList[0].id)
            if(response.data.success) {
                this.$emit("eventDeleted", this.event.id)
                this.onUninscriptEvent()
            }
        },

        onUninscriptEvent() {
            this.isCancel = true
            this.Cancel()


        }
    },

    components: {
        Modal,
        PresentationsEvent,
    }

}

</script>

<style>

</style>