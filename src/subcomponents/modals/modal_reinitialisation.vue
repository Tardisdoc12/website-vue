<template>
    <Modal
        :title="title"
        @close="$emit('cancelSignal')"
    >
        <div class="flex flex-col items-center justify-center space-y-4">
            <p class="text-gray-700">Entrez votre adresse email pour recevoir les instructions de réinitialisation de votre mot de passe.</p>
            <input
                v-model="email"
                type="email"
                placeholder="Votre adresse email"
                class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            <bouton @click="handleRequest" :disabled="!email"
                class="appearance-none button-base"
                :style="{
                    '--btn-bg' : !email ? Couleurs.cyan : Couleurs.main_blue,
                    '--btn-hover-bg' : !email ? Couleurs.cyan : Couleurs.dark_blue
                }"
            >Envoyer</bouton>
        </div>
    </Modal>
</template>

<script>
import Modal from "@/subcomponents/unitary_elements/modalComponent.vue"
import apiUser from "@/javascript/api/users_wp"
import { Couleurs } from '@/javascript/constants/colors'

export default {
    data() {
        return {
            Couleurs,
            isOpen: true,
            title: "Réinitialisation du mot de passe",
            email: "",
        }
    },

    methods: {
        async handleRequest() {
            try {
                const response = await apiUser.request_reset_password(this.email)
                if (response.data.success) {
                    this.$emit('cancelSignal')
                    alert(response.data.message)
                }
            } catch (error) {
                console.error("Erreur lors de la demande de réinitialisation :", error)
                alert("Une erreur est survenue. Veuillez réessayer plus tard.")
            }
        },
    },

    components: {
        Modal
    },
}

</script>

<style>
</style>