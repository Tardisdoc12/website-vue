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
                    '--btn-bg': 'var(--validate-color)',
                    '--btn-hover-bg':'var(--validate-hover-color)',
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
                    '--btn-bg': 'var(--validate-color)',
                    '--btn-hover-bg':'var(--validate-hover-color)',
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
                    '--btn-bg': 'var(--validate-color)',
                    '--btn-hover-bg':'var(--validate-hover-color)',
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
                    <th class="border border-gray-300 p-2 text-center">Encadrant</th>
                    <th v-for="field in fields_to_show" :key="field.nom" class="border border-gray-300 p-2 text-left">{{ field.nom }}</th>
                    <th class="border border-gray-300 p-2 text-center">Statut</th>
                    <th class="border border-gray-300 p-2 text-center">Paiement</th>
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
                    <td class="border border-gray-300 p-2 text-center">{{ user.encadrant }}</td>
                    <td
                        v-for="field in fields_to_show"
                        :key="field.nom"
                        class="border border-gray-300 p-2 text-center"
                    >
                        {{ user.specialField?.[field.nom] ? user.specialField?.[field.nom] : 'non renseigné' }}
                    </td>
                    <td class="border border-gray-300 p-2 text-center">
                        <button
                            v-if="user.status === 'attente'"
                            @click="UpdateUser(user)"
                            :disabled="!hasAttente"
                            class="appearance-none button-base"
                            :style="{
                                '--btn-bg': hasAttente ? 'var(--main-color)' : 'var(--deactivate-button-classic-color)',
                                '--btn-hover-bg': hasAttente ? 'var(--secondary-color)' : 'var(--deactivate-button-classic-color)'
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
                                '--btn-bg': hasAttente ? 'var(--main-color)' : 'var(--deactivate-button-classic-color)',
                                '--btn-hover-bg': hasAttente ? 'var(--secondary-color)' : 'var(--deactivate-button-classic-color)',
                                '--btn-color': '#000000'
                            }"
                        >
                            {{user.status === 'attente' ? 'liste d\'attente' : 'inscrit'}}
                        </button>
                    </td>
                    <td class="border border-gray-300 p-2 text-center">
                        <span v-if="user.payement_status === 'completed'" class="text-[var(--validate-color)] font-semibold">Payé</span>
                        <span v-else-if="user.payement_status === 'cash'" class="text-[var(--validate-color)] font-semibold">Payé en espèces</span>
                        <span v-else class="text-[var(--cancel-color)] font-semibold">Non payé</span>
                    </td>
                    <td class="border border-gray-300 p-2 text-center">
                        <button 
                        @click="DeleteUser(user)"
                        class="appearance-none button-base"
                        :style="{
                            '--btn-bg': 'var(--cancel-color)',
                            '--btn-hover-bg': 'var(--cancel-hover-color)'
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
        },

        eventCategorie: {
            type: String,
            required: false,
            default: ""
        }
    },

    data() {
        
        const categorie = this.$settings.categories.find(category => category.nom === this.eventCategorie)
        const fields_to_show = categorie.champs_speciaux.filter(field => field.affichage_liste)
        return {
            fields_csv: ["Nom", "Email", "Téléphone", "Experience"].concat(fields_to_show),
            isPhoneCopied: false,
            isEmailCopied: false,
            isPhoneError: false,
            isEmailError: false,
            fields_to_show: fields_to_show,
        }
    },

    computed: {
        usersToShow() {
            return this.usersRegistered.map(user => ({
                id: user.id,
                user_name: user.user_name,
                email: user.email,
                phone: user.phone,
                specialField: user.specialField,
                encadrant: user.encadrement === "1" ? "Oui" : "Non",
                status: user.status,
                payement_status: user.payement_status,
                wp_user_id: user.wp_user_id
            }))
        },
    },

    methods: {
        async UpdateUser(user) {
            const response = await api.change_status_inscrit(this.event_id, user.id)
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
                const isAdherent = obj.is_adherent === "1";
                const valueSpecialFieldsToShow = Object.entries(obj.specialField)
                    .filter(([key]) => 
                    (this.fields_to_show.find(f => f.nom === key)?.affichage_adherent && isAdherent)
                    ||
                    (this.fields_to_show.find(f => f.nom === key)?.affichage_non_adherent && !isAdherent)
                )
                
                const valueSpecialFieldToHide= Object.entries(obj.specialField)
                    .filter(([key]) => 
                    !(this.fields_to_show.find(f => f.nom === key)?.affichage_adherent && isAdherent)
                    &&
                    !(this.fields_to_show.find(f => f.nom === key)?.affichage_non_adherent && !isAdherent)
                )

                let SpecialFieldsOrdered = [];
                Object.entries(obj.specialField).forEach(([key, value]) => {
                    if (valueSpecialFieldsToShow.find(([k]) => k === key)) {
                        SpecialFieldsOrdered.push(value);
                    }
                    if (valueSpecialFieldToHide.find(([k]) => k === key)) {
                        SpecialFieldsOrdered.push("");
                    }
                });

                const values = [
                    obj.user_name,
                    obj.email,
                    obj.phone,
                    obj.is_adherent === "0" ? "" : obj.experience,
                    ...SpecialFieldsOrdered,
                ].map(value => `"${String(value).replace(/"/g, '""')}"`);
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