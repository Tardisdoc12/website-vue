<template>
    <DepliantWindow
        :title="titleInformations"
        :backgroundColorOpen="'var(--secondary-color)'"
        :writenColorOpen="Couleurs.white"
        :border-color="'var(--secondary-color)'"
        :border-color-open="'var(--secondary-color)'"
        :is-opoen-forced="true"
        :width="'95%'"
    >
        <div style="margin-left:25px;margin-top: 10px;">
            <div v-if="!isInformationsChange">
                <li><strong>Nom :</strong> {{ user.lastName || "—" }}</li>
                <li><strong>Prénom :</strong> {{ user.firstName || "—" }}</li>
                <li><strong>Email :</strong> {{ user.email || "—" }}</li>
                <li><strong>Téléphone :</strong> {{ user.telephone || "—" }}</li>
                <li><strong>Moto :</strong> {{ user.moto || "—" }}</li>
                <li><strong>Statut :</strong> {{ isAdherentComp ? "Adhérent" : "Non-adhérent" }}</li>
            </div>
            <div
                v-if="isInformationsChange"
                :style= "{
                    display: 'flex',
                    'flex-direction': 'column',
                    gap: '2px',
                }"
            >
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
                <li><strong>Statut :</strong> {{ isAdherentComp ? "Adhérent" : "Non-adhérent" }}</li>
            </div>
            
        </div>
        <div class="button-container" v-if="canUpdate">
            <button
                v-if="isInformationsChange"
                class="appearance-none"
                :style="{
                    display: 'inline-block',
                    color: 'var(--main-color)',
                    border: '1px solid ' + `${'var(--main-color)'}`,
                    padding: '0.3rem 0.3rem',
                    borderRadius: '0.375rem',
                    backgroundColor: Couleurs.white,
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
                    color: 'var(--main-color)',
                    border: '1px solid ' + `${'var(--main-color)'}`,
                    padding: '0.3rem 0.3rem',
                    borderRadius: '0.375rem',
                    backgroundColor: Couleurs.white,
                }"
                @click="modifierInformations"
            >
                Modifier mes informations
            </button>
        </div>
    </DepliantWindow>

    <EventsUser
        v-if="canUpdate"
        :user="DataUser"
        style="width: 95%;"
    />

    <DepliantWindow
        :title="titleUrgences"
        :backgroundColorOpen="'var(--secondary-color)'"
        :writenColorOpen="'#FFFFFF'"
        :border-color="'var(--secondary-color)'"
        :border-color-open="'var(--secondary-color)'"
        :is-opoen-forced="true"
        :width="'95%'"
    >
        <div style="margin-left:25px;margin-top: 5px;">
            <div v-if="!isUrgencesChange">
                <p><strong>Nom et Prénom du contact d’urgence :</strong> {{ user.urgence_name || "—" }}</p>
                <p><strong>Téléphone du contact d’urgence :</strong> {{ user.urgence_phone || "—" }}</p>
            </div>
            <div
                v-else
                :style= "{
                    display: 'flex',
                    'flex-direction': 'column',
                    gap: '2px',
                }"
            >
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
        <div class="button-container" v-if="canUpdate">
            <button
                v-if="isUrgencesChange"
                class="appearance-none"
                :style="{
                    display: 'inline-block',
                    color: 'var(--main-color)',
                    border: '1px solid ' + 'var(--main-color)',
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
                    color: 'var(--main-color)',
                    border: '1px solid ' + 'var(--main-color)',
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
import DepliantWindow from '@/subcomponents/unitary_elements/depliantWindow.vue';
import { Couleurs } from '@/javascript/constants/colors'
import EventsUser from '@/subcomponents/depliants/depliant_events.vue'
import { isAdherent } from "@/javascript/constants/roles"

export default {
    emits: [
        'userChange'
    ],

    props: {
        DataUser: {
            required: true,
            type: Object
        },
        canUpdate: {
            type: Boolean,
            required : false,
            default: true
        }
    },

    data() {
        return {
            Couleurs,
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
        isAdherentComp() {
            return isAdherent(this.user.roles)
        }
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
        DepliantWindow,
        EventsUser
    }
}

</script>

<style>
.oval-input {
  flex: 1;
  padding: 0.1rem 0.3rem;
  border: 1px solid #ccc;
  border-radius: 5px;
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