<template>
    <div class="max-w-md mx-auto p-4 bg-white shadow rounded">
        <h2 class="text-xl font-bold mb-4" style="text-align:center;">Créer un Compte</h2>
        <form @submit.prevent="handleSubmit" class="space-y-4">
            <div style="margin-left: 20px; margin-right: 20px;margin-top: 10px;">

                <!-- Nom de l'utilisateur -->
                <div class="flex flex-col gap-1">
                    <label class="block font-medium">
                        Prénom
                    </label>
                    <input
                        v-model="formUser.firstName"
                        type="text"
                        class="w-full border p-1 rounded"
                        required
                    />
                </div>

                <div class="flex flex-col gap-1">
                    <label class="block font-medium">
                        Nom
                    </label>
                    <input
                        v-model="formUser.lastName"
                        type="text"
                        class="w-full border p-1 rounded"
                        required
                    />
                </div>

                
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
            
                <!-- phone -->
                <div class="flex flex-col gap-1">
                    <label class="block font-medium">
                        Téléphone
                    </label>
                    <input
                        v-model="formUser.phone"
                        type="tel"
                        class="w-full border p-1 rounded"
                        pattern="[0-9]{10}"
                        required
                    />
                </div>

                <!-- Moto/Cylindré -->
                <div class="flex flex-col gap-1">
                    <label class="block font-medium">
                        Moto/Cylindrée
                    </label>
                    <input
                        v-model="formUser.bike"
                        type="text"
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

                <!-- Confimed Password -->
                <div class="flex flex-col gap-1">
                    <label class="block font-medium">
                        Confirmer le mot de passe
                    </label>
                    <input
                        v-model="formUser.confirmPassword"
                        type="password"
                        class="w-full border p-1 rounded"
                        minlength="8"
                        required
                    />
                    <small
                        v-if="!passwordMatch()"
                        :class="'text-red-600'"
                    >
                        {{ 'Les mots de passe ne correspondent pas' }}
                    </small>
                </div>

                <div class="flex items-center justify-center" style="margin-top: 20px;">
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Créer son compte
                    </button>
                </div>

            </div>
        </form>
    </div>
</template>

<script>
import api from "../javascript/users_wp"

export default {
    data() {
        return {
            formUser: {
                firstName:"",
                lastName:"",
                phone:"",
                bike:"",
                email:"",
                password:"",
                confirmPassword:"",
            },
        }
    },

    methods:{
        passwordMatch() {
            const pwd = (this.formUser.password || '').trim();
            const confirm = (this.formUser.confirmPassword || '').trim();
            return pwd.length > 0 && pwd === confirm;
        },

        async handleSubmit() {
            if (!this.passwordMatch()) {
                return
            }
            const body = {
                    username:this.formUser.firstName + "." + this.formUser.lastName,
                    firstName:this.formUser.firstName,
                    lastName: this.formUser.lastName,
                    email: this.formUser.email,
                    password: this.formUser.password,
                    telephone: this.formUser.phone,
                    moto: this.formUser.bike,
            }
            try{
                const response = await api.create_user(body)
                this.formUser= {
                    firstName:"",
                    lastName:"",
                    phone:"",
                    bike:"",
                    email:"",
                    password:"",
                    confirmPassword:"",
                }
            } catch (err) {
                alert(err.response.data.message)
            }
        }
    },

    computed: {
        passwordRules() {
            const pwd = this.formUser.password;

            return [
                { text: "Au moins 8 caractères", valid: pwd.length >= 8 },
                { text: "Au moins 1 majuscule", valid: /[A-Z]/.test(pwd) },
                { text: "Au moins 1 minuscule", valid: /[a-z]/.test(pwd) },
                { text: "Au moins 1 chiffre", valid: /[0-9]/.test(pwd) },
                { text: "Au moins 1 caractère spécial (*!/@.)", valid: /[*!/@.]/.test(pwd) },
            ];
        },
    }
}

</script>

<style>
</style>