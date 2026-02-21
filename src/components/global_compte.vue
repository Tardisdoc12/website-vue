<template>
    <div class="flex flex-col items-center w-full space-y-2 px-4">
        <!-- Ligne cliquable -->
        <div class="switch-link w-full max-w-md text-center" v-if="!isAuthenticated">
            <p v-if="!hasAccount" @click="hasAccount = true" class="cursor-pointer">
                J’ai déjà un compte
            </p>
            <p v-else-if="hasAccount" @click="hasAccount = false" class="cursor-pointer">
                Je crée un compte
            </p>
        </div>

        
        <!-- Bouton déconnexion si connecté -->
        <div v-else class="w-full max-w-md text-center">
            <p @click="logout" class="logout-link cursor-pointer">Se déconnecter</p>
        </div>
        
        <!-- Sate Account -->
         <div class="w-full">
            <CreateAccount
                v-if="!isAuthenticated && !hasAccount"
                :onSuccess="createAccountSuccess"
                class="w-full max-w-md"
            />
            <ConnectAccount
                v-else-if="!isAuthenticated && hasAccount"
                :onSuccess="loginSuccess"
                class="w-full max-w-md"
            />
            <div
                v-else
                class="w-full"
            >
                <ShowProfil/>
            </div>
        </div>
        <div class="w-full max-w-md text-center">
            <p
                v-if="!isAuthenticated && hasAccount"
                @click="StartForgetPwd"
                class="cursor-pointer"
            >
                J'ai oublié mon mot de passe
            </p>
        </div>
    </div>

    <ModalResetPassword v-if="isForgetPassword" @cancelSignal="Cancel"/>
</template>

<script>
import CreateAccount from "@/components/sign_in.vue"
import ConnectAccount from "@/components/connexion.vue"
import ShowProfil from "@/components/espace_profil.vue"
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
        ShowProfil,
        ModalResetPassword
    }
}

</script>

<style>

</style>