<template>
    <div class="flex flex-col items-center justify-center space-y-2">
        <!-- Ligne cliquable -->
        <div class="switch-link" v-if="!isAuthenticated">
            <p v-if="!hasAccount" @click="hasAccount = true">
                J’ai déjà un compte
            </p>
            <p v-else-if="hasAccount" @click="hasAccount = false">
                Je crée un compte
            </p>
        </div>

        
        <!-- Bouton déconnexion si connecté -->
        <div v-else>
            <p @click="logout" class="logout-link">Se déconnecter</p>
        </div>
        
        <!-- Sate Account -->
        <CreateAccount v-if="(!isAuthenticated && !hasAccount)" :onSuccess="createAccountSuccess"/>
        <ConnectAccount v-else-if="(!isAuthenticated && hasAccount)" :onSuccess="loginSuccess"/>
        <DrawAccount v-else class="w-full max-w-md"/>
        <div class="flex flex-col items-center justify-center space-y-2">
            <p v-if="(!isAuthenticated && hasAccount)" @click="StartForgetPwd">
                J'ai oublié mon mot de passe
            </p>
        </div>
    </div>

    <ModalResetPassword v-if="isForgetPassword" @cancelSignal="Cancel"/>
</template>

<script>
import CreateAccount from "@/components/sign_in.vue"
import ConnectAccount from "@/components/connexion.vue"
import DrawAccount from "@/components/gestions_comptes.vue"
import ModalResetPassword from "@/subcomponents/modals/modal_reinitialisation.vue"
import { jwtDecode } from "jwt-decode"

export default {
    
    data() {
        return {
            hasAccount: true,
            token: sessionStorage.getItem("mps_moto"),
            isForgetPassword: false,
        }
    },

    computed: {
        isAuthenticated() {
            if (!this.token) return false

            try {
                const decoded = jwtDecode(this.token)
                const now = Date.now() / 1000
                return decoded.exp && decoded.exp > now
            } catch (e) {
                console.error("JWT invalide :", e)
                sessionStorage.removeItem("mps_moto")
                return false
            }
        },
    },

    methods: {
        StartForgetPwd() {
            this.isForgetPassword = true
        },

        Cancel() {
            this.isForgetPassword = false
        },

        logout() {
            sessionStorage.removeItem("mps_moto")
            this.token = null
            this.hasAccount = true
        },
        loginSuccess(token) {
            this.token = token
        },
        createAccountSuccess() {
            this.hasAccount=true
        }
    },

    components: {
        CreateAccount,
        ConnectAccount,
        DrawAccount,
        ModalResetPassword
    }
}

</script>

<style>

</style>