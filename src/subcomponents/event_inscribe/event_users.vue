<template>
    <div 
        :style="{
            display: 'flex',
            'flex-direction': 'column',
            gap: '2px',
            padding: '10px 5px'
        }"
    >
        <!-- Les boutons de gestions des comptes -->
        <div
            class="flex items-center justify-start space-x-4"
        >
            <!-- bouton pour copier téléphone des inscrits -->
            <button
                type="button"
                class="appearance-none button-base"
                :style="{
                    '--btn-bg': Couleurs.vert,
                    '--btn-hover-bg':Couleurs.dark_vert,
                }"
                @click="CopyPhoneOrEmail(true)"
            >
                <font-awesome-icon icon="fa-solid fa-phone-volume" v-if="!isPhoneCopied && !isPhoneError"/>
                <font-awesome-icon icon="fa-solid fa-check" v-if="isPhoneCopied"/>
                <font-awesome-icon icon="fa-solid fa-xmark" v-if="isPhoneError"/>
            </button>

            <!-- button pour copier mail inscrits -->
            <button
                type="button"
                class="appearance-none button-base"
                :style="{
                    '--btn-bg': Couleurs.vert,
                    '--btn-hover-bg':Couleurs.dark_vert,
                }"
                @click="CopyPhoneOrEmail(false)"
            >
                <font-awesome-icon icon="fa-solid fa-envelope" v-if="!isEmailCopied && !isEmailError"/>
                <font-awesome-icon icon="fa-solid fa-check" v-if="isEmailCopied"/>
                <font-awesome-icon icon="fa-solid fa-xmark" v-if="isEmailError"/>
            </button>

            <!-- Le bouton d'export en csv -->
            <button
                v-if="usersToShow.length !== 0"
                type="button"
                class="appearance-none button-base"
                :style="{
                    '--btn-bg': Couleurs.vert,
                    '--btn-hover-bg':Couleurs.dark_vert,
                }"
                @click="downloadCSV"
            >
                <font-awesome-icon icon="fa-solid fa-download" />
            </button>
        </div>

        <div v-if="usersToShow.length === 0" style="margin-top: 10px;">
            Aucun utilisateur
        </div>

        <div v-else class="overflow-auto max-h-[300px] max-w-full border border-gray-300">
            <table class="min-w-full border-collapse border border-gray-300 mt-2">
                <thead>
                    <tr class="bg-gray-100">
                    <th class="border border-gray-300 p-2 text-left">Nom</th>
                    <th class="border border-gray-300 p-2 text-left">Email</th>
                    <th class="border border-gray-300 p-2 text-left">Téléphone</th>
                    <th class="border border-gray-300 p-2 text-left">Thème demandé</th>
                    <th class="border border-gray-300 p-2 text-left">Moto</th>
                    <th class="border border-gray-300 p-2 text-left">Expérience</th>
                    <th class="border border-gray-300 p-2 text-center">Encadrant</th>
                    <th class="border border-gray-300 p-2 text-center">Statut</th>
                    <th class="border border-gray-300 p-2 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr 
                    v-for="(user, index) in usersToShow" 
                    :key="user.id || index"
                    class="hover:bg-gray-50"
                    >
                    <td class="border border-gray-300 p-2">{{ user.user_name }}</td>
                    <td class="border border-gray-300 p-2">{{ user.email }}</td>
                    <td class="border border-gray-300 p-2">{{ user.phone }}</td>
                    <td class="border border-gray-300 p-2">{{ user.goal }}</td>
                    <td class="border border-gray-300 p-2">{{ user.bike }}</td>
                    <td class="border border-gray-300 p-2">{{ user.experience }}</td>
                    <td class="border border-gray-300 p-2 text-center">{{ user.encadrant }}</td>
                    <td class="border border-gray-300 p-2 text-center">
                        <button
                            v-if="user.status === 'attente'"
                            @click="UpdateUser(user)"
                            :disabled="!hasAttente"
                            class="appearance-none button-base"
                            :style="{
                                '--btn-bg': hasAttente ? Couleurs.main_blue : Couleurs.gray,
                                '--btn-hover-bg': hasAttente ? Couleurs.dark_blue : Couleurs.gray
                            }"
                        >
                            {{user.status === 'attente' ? 'liste d\'attente' : 'inscrit'}}
                        </button>
                        <button
                            v-if="user.status === 'inscrit'"
                            @click="UpdateUser(user)"
                            class="appearance-none button-base"
                            :disabled="!hasAttente"
                            :style="{
                                '--btn-bg': hasAttente ? Couleurs.main_blue : Couleurs.gray,
                                '--btn-hover-bg': hasAttente ? Couleurs.dark_blue : Couleurs.gray
                            }"
                        >
                            {{user.status === 'attente' ? 'liste d\'attente' : 'inscrit'}}
                        </button>
                    </td>
                    <td class="border border-gray-300 p-2 text-center">
                        <button 
                        @click="DeleteUser(user)"
                        class="appearance-none button-base"
                        :style="{
                            '--btn-bg': Couleurs.main_red,
                            '--btn-hover-bg': Couleurs.dark_red
                        }"
                        >
                            Supprimer l'inscrit
                        </button>
                    </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
import api from "@/javascript/api/axios_inscription"
import { Couleurs } from "@/javascript/constants/colors"

export default {
    props: {
        usersRegistered: {
            type: Array,
            default:[],
        },

        event_id: {
            type: Number,
            required: true
        },

        hasAttente: {
            type: Boolean,
            default: false
        }
    },

    data() {
        return {
            Couleurs,
            fields_csv: ["Nom", "Email", "Téléphone", "Thème demandé", "Moto", "Experience"],
            isPhoneCopied: false,
            isEmailCopied: false,
            isPhoneError: false,
            isEmailError: false,
        }
    },

    computed: {
        usersToShow() {
            return this.usersRegistered.map(user => ({
                id: user.id,
                user_name: user.user_name,
                email: user.email,
                phone: user.phone,
                goal: user.goal,
                bike: user.bike,
                experience: user.is_adherent === "1" ? "" : user.experience,
                encadrant: user.encadrement === "1" ? "Oui" : "Non",
                status: user.status,
                wp_user_id: user.wp_user_id
            }))
        },
    },

    methods: {
        async UpdateUser(user) {
            const response = await api.change_status_inscrit(this.event_id, user.email)
            if(response.data.success) {
                if (user.status === "inscrit") {
                    user.status = "attente"
                } else {
                    user.status = "inscrit"
                }
                const data_to_update = {
                    id: user.id,
                    status: user.status,
                    event_id: this.event_id
                }
                this.$emit("userUpdated", data_to_update)
            }
        },

        async CopyPhoneOrEmail(isPhone) {
            try {
                let TextToCopy = "";
                this.usersRegistered.forEach(user => {
                    if (isPhone) {
                        TextToCopy += `${user.phone}, `;
                    } else {
                        TextToCopy += `${user.email}, `;
                    }
                });
                await navigator.clipboard.writeText(TextToCopy);
                if (isPhone) {
                    this.isPhoneCopied = true;
                } else {
                    this.isEmailCopied = true;
                }
            } catch (err) {
                console.error('Failed to copy text: ', err);
                if (isPhone) {
                    this.isPhoneError = true;
                } else {
                    this.isEmailError = true;
                }
            }
        },

        downloadCSV() {
            const headers = this.fields_csv.map(h => `"${h}"`);
            
            const rows = this.usersRegistered.map(obj => {
                const values = [
                    obj.user_name,
                    obj.email,
                    obj.phone,
                    obj.goal,
                    obj.bike,
                    obj.is_adherent === "0" ? "" : obj.experience
                ].map(value => `"${String(value).replace(/"/g, '""')}"`); // Échappe les guillemets
                return values.join(",");
            });

            const csv = "\uFEFF" + [headers.join(","), ...rows].join("\n"); // BOM UTF-8

            const blob = new Blob([csv], { type: "text/csv;charset=utf-8;"})
            const url = URL.createObjectURL(blob)
            const link = document.createElement("a")

            link.href = url
            link.setAttribute("download","inscrits.csv")
            document.body.appendChild(link)
            link.click()
            document.body.removeChild(link)
        },

        async DeleteUser(user) {
            const response = await api.delete_inscrit(this.event_id, user.id)
            if(response.data.success) {
                this.$emit("userDeleted", user)
            }
        }
    }
}

</script>

<style>
</style>