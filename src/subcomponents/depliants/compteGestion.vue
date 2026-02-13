<template>
    <DepliantWindow
        :title="titleInformations"
        :backgroundColorOpen="'#2d5c7f'"
        :writenColorOpen="'#FFFFFF'"
        :border-color="'#2d5c7f'"
        :border-color-open="'#2d5c7f'"
        :is-opoen-forced="true"
        :width="'95%'"
    >
        <div style="margin-left:25px;margin-top: 10px;">
            <div v-if="!isInformationsChange">
                <li><strong>Nom :</strong> {{ user.lastName }}</li>
                <li><strong>Prénom :</strong> {{ user.firstName }}</li>
                <li><strong>Email :</strong> {{ user.email }}</li>
                <li><strong>Téléphone :</strong> {{ user.telephone }}</li>
                <li><strong>Moto :</strong> {{ user.moto }}</li>
            </div>
            <div v-if="isInformationsChange">
                <li>
                    <label><strong>Nom :</strong></label>
                    <input
                        v-model="user.lastName"
                        class="oval-input"
                    />
                </li>
                <li>
                    <label><strong>Prénom :</strong></label>
                    <input
                        v-model="user.firstName"
                        class="oval-input"
                    />
                </li>
                <li>
                    <label><strong>Email :</strong></label>
                    <input
                        v-model="user.email"
                        class="oval-input"
                    />
                </li>
                <li>
                    <label><strong>Téléphone :</strong></label>
                    <input
                        v-model="user.telephone"
                        class="oval-input"
                    />
                </li>
                <li>
                    <label><strong>Moto :</strong></label>
                    <input
                        v-model="user.moto"
                        class="oval-input"
                    />
                </li>
            </div>
            
        </div>
        <div class="button-container">
            <button
                v-if="isInformationsChange"
                class="appearance-none"
                :style="{
                    display: 'inline-block',
                    color: '#245473',
                    border: '1px solid #245473',
                    padding: '0.3rem 0.3rem',
                    borderRadius: '0.375rem',
                    backgroundColor: 'white',
                }"
                @click="validationChangement"
            >
                Sauvegarder les changements
            </button>
            <button
                v-else
                class="appearance-none"
                :style="{
                    display: 'inline-block',
                    color: '#245473',
                    border: '1px solid #245473',
                    padding: '0.3rem 0.3rem',
                    borderRadius: '0.375rem',
                    backgroundColor: 'white',
                }"
                @click="modifierInformations"
            >
                Modifier mes informations
            </button>
        </div>
    </DepliantWindow>

    <DepliantWindow
        :title="titleUrgences"
        :backgroundColorOpen="'#2d5c7f'"
        :writenColorOpen="'#FFFFFF'"
        :border-color="'#2d5c7f'"
        :border-color-open="'#2d5c7f'"
        :is-opoen-forced="true"
        :width="'95%'"
    >
        <div style="margin-left:25px;margin-top: 5px;">
            <div v-if="!isUrgencesChange">
                <p><strong>Nom et Prénom du contact d’urgence :</strong> {{ user.urgence_name || "—" }}</p>
                <p><strong>Téléphone du contact d’urgence :</strong> {{ user.urgence_phone || "—" }}</p>
            </div>
            <div v-else>
                <li>
                    <label><strong> Nom et Prénom du contact d’urgence :</strong></label>
                    <input
                        v-model="user.urgence_name"
                        class="oval-input"
                    />
                </li>
                <li>
                    <label><strong> Téléphone du contact d’urgence :</strong></label>
                    <input
                        v-model="user.urgence_phone"
                        class="oval-input"
                    />
                </li>
            </div>
        </div>
        <div class="button-container">
            <button
                v-if="isUrgencesChange"
                class="appearance-none"
                :style="{
                    display: 'inline-block',
                    color: '#245473',
                    border: '1px solid #245473',
                    padding: '0.3rem 0.3rem',
                    borderRadius: '0.375rem',
                    backgroundColor: 'white',
                }"
                @click="validationChangement"
            >
                Sauvegarder les changements
            </button>
            <button
                v-else
                class="appearance-none"
                :style="{
                    display: 'inline-block',
                    color: '#245473',
                    border: '1px solid #245473',
                    padding: '0.3rem 0.3rem',
                    borderRadius: '0.375rem',
                    backgroundColor: 'white',
                }"
                @click="modifierUrgences"
            >
                Modifier mes informations
            </button>
        </div>
    </DepliantWindow>
</template>

<script>
import api_user from "@/javascript/api/users_wp.js"
import DepliantWindow from './unitary_elements/depliantWindow.vue';

export default {

    props: {
        DataUser: {
            required: true,
            type: Object
        }
    },

    data() {
        return {
            titleInformations: "Mes Informations",
            titleUrgences: "Urgences",
            isInformationsChange: false,
            isUrgencesChange: false,
        }
    },

    computed: {
        user() {
            return this.DataUser
        },
    },

    methods: {
        async validationChangement() {
            if(this.isInformationsChange)
            {
                this.isInformationsChange = !this.isInformationsChange
            }
            if(this.isUrgencesChange)
            {
                this.isUrgencesChange = !this.isUrgencesChange
            }

            try {
                const response = await api_user.update_user(this.user)
                if (response.data.success) {
                    this.$emit("userChange", this.user)
                }
            } catch (err) {
                console.error(err)
                return
            }
        },

        modifierInformations() {
            this.isInformationsChange = !this.isInformationsChange
        },

        modifierUrgences() {
            this.isUrgencesChange = !this.isUrgencesChange
        }
    },

    components: {
        DepliantWindow
    }
}

</script>

<style>
.oval-input {
  flex: 1;
  padding: 0.3rem 0.5rem;
  border: 1px solid #ccc;
  border-radius: 9999px;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.oval-input:focus {
  border-color: #007bff;
  box-shadow: 0 0 4px rgba(0, 123, 255, 0.4);
}

.button-container {
  display: flex;
  justify-content: center;
  margin-top: 15px;
  margin-bottom: 10px;
}
</style>