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

        <div v-else style="max-height: 300px; overflow-y: auto;">
            <div style="margin-top: 10px;"></div>
            <div 
                v-for="(user, index) in userRegister" 
                :key="user.id || index" 
                class="p-2 mb-2 border rounded shadow"
            >
                <p @click="DeleteUser(user)">{{ "Supprimer l'inscrit" }}</p>
                <p><strong>Nom :</strong> {{ user.user_name }}</p>
                <p><strong>Email :</strong> {{ user.email }}</p>
                <p><strong>Téléphone :</strong> {{ user.phone }}</p>
                <p><strong>thème demandé :</strong> {{ user.goal }}</p>
                <p><strong>Expérience :</strong> {{ user.experience }}</p>
            </div>
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
            fields_csv: ["Nom", "Email", "Téléphone", "Thème demandé", "Experience"],
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