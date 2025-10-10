<template>
    <Modal
        :title="title"
        :isCancel="isOpen"
        @changeBool="Cancel"
    >
        <button
            v-if="userRegister.length !== 0"
            style="margin-top: 10px;"
            type="button"
            class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600"
            @click="downloadCSV"
        >
            <font-awesome-icon icon="fa-solid fa-download" />
        </button>

        <div v-if="userRegister.length === 0" style="margin-top: 10px;">
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
                    <th class="border border-gray-300 p-2 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr 
                    v-for="(user, index) in userRegister" 
                    :key="user.id || index"
                    class="hover:bg-gray-50"
                    >
                    <td class="border border-gray-300 p-2">{{ user.user_name }}</td>
                    <td class="border border-gray-300 p-2">{{ user.email }}</td>
                    <td class="border border-gray-300 p-2">{{ user.phone }}</td>
                    <td class="border border-gray-300 p-2">{{ user.goal }}</td>
                    <td class="border border-gray-300 p-2">{{ user.bike }}</td>
                    <td class="border border-gray-300 p-2">{{ user.experience }}</td>
                    <td class="border border-gray-300 p-2 text-center">
                        <button 
                        @click="DeleteUser(user)"
                        class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded"
                        >
                            Supprimer l'inscrit
                        </button>
                    </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </Modal>
</template>

<script>
import Modal from "./unitary_elements/modalComponent.vue"
import api from "@/javascript/axios_inscription"

export default {
    props: {
        userRegister: {
            type: Array,
            default:[],
        },

        event_id: {
            type: Number,
            required: true
        }
    },

    data() {
        return {
            title: "Visualisation des Inscrits",
            isOpen: true,
            fields_csv: ["Nom", "Email", "Téléphone", "Thème demandé", "Moto", "Experience"],
        }
    },

    methods: {
        downloadCSV() {
            const headers = this.fields_csv.map(h => `"${h}"`);
            
            const rows = this.userRegister.map(obj => {
                const values = [
                    obj.user_name,
                    obj.email,
                    obj.phone,
                    obj.goal,
                    obj.bike,
                    obj.experience
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

        Cancel() {
            this.$emit('cancelSignal')
        },

        async DeleteUser(user) {
            const response = await api.delete_inscrit(this.event_id, user.id)
            alert("la personne à était retirer des inscrits veuillez recharger la liste pour voir la modification")
        }
    },

    components: {
        Modal
    }
}

</script>

<style>
</style>