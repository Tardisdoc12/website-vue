<template>
    <div class="flex flex-col items-center justify-center space-y-4">
        <h2 class="text-2xl font-bold">Réinitialisation du mot de passe</h2>
        <form @submit.prevent="handleSubmit" class="space-y-4">
            
            <div class="flex flex-col gap-1">
                <label class="block font-medium">
                    Nouveau mot de passe
                    <span style="color: red;">*</span>
                </label>
                <input
                    v-model="password"
                    :type="showPassword ? 'text' : 'password'"
                    class="w-full border p-1 rounded"
                    minlength="8"
                    required
                />

                <!-- Bouton œil à l'intérieur de l'input -->
                <button
                    type="button"
                    @click="showPassword = !showPassword"
                    tabindex="-1"
                >
                    <span v-if="showPassword">👁️</span>
                    <span v-else>🙈</span>
                </button>

                <ul class="text-xs mt-1 space-y-1">
                    <span>{{ "Doit contenir :" }}</span>
                    <li
                        v-for="rule in passwordRules"
                        :key="rule.text"
                        :class="rule.valid ? 'text-green-600' : 'text-gray-500'"
                        style="padding: 0px 8px"
                    >
                        {{ rule.text }}
                    </li>
                </ul>
            </div>


            <!-- Password -->
            <div class="flex flex-col gap-1">
                <label class="block font-medium">
                    Répéter le nouveau mot de passe
                </label>
                <input
                    v-model="repeatPassword"
                    type="password"
                    class="w-full border p-1 rounded"
                    minlength="8"
                    required
                />
            </div>

            <div class="flex items-center justify-center" style="margin-top: 20px;">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Réinitialiser le mot de passe
                </button>
            </div>
        </form>
    </div>
</template>

<script>
import apiUser from "@/javascript/api/users_wp"

export default {
    name: "ResetPasswordPage",
    data() {
        return {
            // Aucune donnée spécifique pour cette page
            isLegitimate: false,
            password: "",
            showPassword: false,
            repeatPassword: "",
        }
    },


    async mounted() {
        try {
        // Vérification de la légitimité de l'accès à cette page
        const params = new URLSearchParams(window.location.search)

        const key = params.get('key')
        const login = params.get('login')
        if (key && login) {
            const response = await apiUser.verify_reset(key, login)
            
            if (response?.data?.success !== true) {
                window.location.replace("/")
            }
            this.isLegitimate = response.data.success
        } 
        }
        catch (error) {
            window.location.replace("/")
        }
    },

    computed: {
        passwordMatch() {
            const pwd = (this.password || '').trim();
            const confirm = (this.repeatPassword || '').trim();
            return pwd.length > 0 && pwd === confirm;
        },
        passwordRules() {
            const pwd = this.password;

            return [
                { text: "Au moins 8 caractères", valid: pwd.length >= 8 },
                { text: "Au moins 1 majuscule", valid: /[A-Z]/.test(pwd) },
                { text: "Au moins 1 minuscule", valid: /[a-z]/.test(pwd) },
                { text: "Au moins 1 chiffre", valid: /[0-9]/.test(pwd) },
                { text: `Au moins 1 caractère spécial (*!/@.'"#;?&)`, valid: /[*!/@.'"#;?&]/.test(pwd) },
            ];
        },
    },

    methods: {
        async handleSubmit() {
            if (!this.passwordMatch) {
                alert("Les mots de passe ne correspondent pas.")
                return
            }
            // Appel à l'API pour réinitialiser le mot de passe
            const params = new URLSearchParams(window.location.search)
            const key = params.get('key')
            const login = params.get('login')
            try {
                const response = await apiUser.reset_password(key, login, this.password)
                if (response.data.success) {
                    alert("Mot de passe réinitialisé avec succès. Vous pouvez maintenant vous connecter.")
                    window.location.replace("/espace-adherent")
                } else {
                    alert("Une erreur est survenue lors de la réinitialisation du mot de passe.")
                }
            } catch (error) {
                console.error("Erreur lors de la réinitialisation du mot de passe :", error)
                alert("Une erreur est survenue. Veuillez réessayer plus tard.")
            }
        }
    }
}
</script>

<style>
</style>