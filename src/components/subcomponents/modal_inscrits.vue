<template>
    <Modal
        :title="title"
        :isCancel="isOpen"
        @changeBool="Cancel"
    >
        <div v-if="userRegister.length === 0">
            Aucun utilisateur
        </div>

        <div v-else>
            <div style="margin-top: 10px;"></div>
            <div 
                v-for="(user, index) in userRegister" 
                :key="user.id || index" 
                class="p-2 mb-2 border rounded shadow"
            >
                <p><strong>Nom :</strong> {{ user.user_name }}</p>
                <p><strong>Email :</strong> {{ user.email }}</p>
                <p><strong>Téléphone :</strong> {{ user.phone }}</p>
                <p><strong>Objectif :</strong> {{ user.goal }}</p>
                <p><strong>Experience :</strong> {{ user.experience }}</p>
            </div>
        </div>
    </Modal>
</template>

<script>
import Modal from "./unitary_elements/modalComponent.vue"

export default {
    props: {
        userRegister: {
            type: Array,
            default:[],
        }
    },

    data() {
        return {
            title: "Visualisation des Inscrits",
            isOpen: true,
        }
    },

    methods: {
        Cancel() {
            this.$emit('cancelSignal')
        },
    },

    components: {
        Modal
    }
}

</script>

<style>
</style>