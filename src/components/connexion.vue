<template>
    <div class="max-w-md mx-auto p-4 bg-white shadow rounded">
        <h2 class="text-xl font-bold mb-4">Connexion</h2>
        <form @submit.prevent="handleSubmit" class="space-y-4">
            
            <!-- email -->
            <div class="flex flex-col gap-1">
                <label class="block font-medium">
                    E-mail
                </label>
                <input
                    v-model="formUser.email"
                    type="email"
                    class="w-full border p-1 rounded"
                    required
                />
            </div>

            <!-- Password -->
            <div class="flex flex-col gap-1">
                <label class="block font-medium">
                    Mot de passe
                </label>
                <input
                    v-model="formUser.password"
                    type="password"
                    class="w-full border p-1 rounded"
                    minlength="8"
                    required
                />
            </div>

            <div class="flex items-center justify-center" style="margin-top: 20px;">
                <button type="submit" class="appearance-none button-base">
                    Connexion
                </button>
            </div>

        </form>
    </div>
</template>

<script>
import apiWP from "@/javascript/api/users_wp"
import { jwtDecode } from "jwt-decode"

export default {
    props: {
        onSuccess: {
            type: Function,
            default: null
        }
    },
    data() {
        return {
            formUser: {
                email:"",
                password: ""
            }
        }
    },
    
    methods: {
        async handleSubmit() {
            try {
                const data = await apiWP.verify_connexion(this.formUser.email, this.formUser.password)
                sessionStorage.setItem("mps_moto", data.token)
                const decoded = jwtDecode(data.token)
                const user_id = decoded.data.user.id
                const setUserWP = await apiWP.connect_user(user_id)
                if (this.onSuccess) {
                    this.onSuccess(data.token)
                }
            }catch (err) {
                console.error("❌ Erreur login:", err)
            }
        }
    }
}

</script>

<style>
</style>