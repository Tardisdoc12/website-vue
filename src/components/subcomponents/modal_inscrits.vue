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
                <p @click="DeleteUser(user)">{{ "Supprimer l'inscrit" }}</p>
                <p><strong>Nom :</strong> {{ user.user_name }}</p>
                <p><strong>Email :</strong> {{ user.email }}</p>
                <p><strong>Téléphone :</strong> {{ user.phone }}</p>
                <p><strong>thème demandé :</strong> {{ user.goal }}</p>
                <p><strong>Expérience :</strong> {{ user.experience }}</p>
            </div>
        </div>
    </Modal>
</template>

<script>
import Modal from "./unitary_elements/modalComponent.vue"
import api from "@/javascript/axios_inscription"

export default {
    props: {
        userRegister: {
            type: Array,
            default:[],
        },

        event_id: {
            type: Number,
            required: true
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
        async DeleteUser(user) {
            const response = await api.delete_inscrit(this.event_id, user.id)
            alert("la personne à était retirer des inscrits veuillez recharger la liste pour voir la modification")
        }
    },

    components: {
        Modal
    }
}

</script>

<style>
</style>