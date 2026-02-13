<template>
    <div v-if="isAdherent">
        <h2>Pour devenir Adherent</h2>
        <iframe style="width: 100%;" src="https://www.helloasso.com/associations/mps-moto/adhesions/devenir-adherent/widget"></iframe>
    </div>
    <div v-else>
        <p>{{ "En progrès" }}</p>
    </div>
</template>

<script>
import { jwtDecode } from "jwt-decode"
import api from "@/javascript/api/users_wp.js"

export default {
    data() {
        return {
            user: {},
        }
    },

    async mounted() {
        const token = sessionStorage.getItem("mps_moto")
        if (token) {
            const decoded = jwtDecode(token)
            const user_id = decoded.data.user.id
            const user_info = await api.get_user(user_id)
            this.user = user_info.user
        }
    },

    computed: {
        isAdherent() {
            if(this.user?.roles) {
                if (!this.user.roles.includes("non_adherent")) {
                    return true
                }
            }
            return false
        }
    }
}

</script>

<style>
</style>