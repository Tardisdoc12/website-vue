<template>
    <Modal
        :title="title"
        :isCancel="isOpen"
        @changeBool="Cancel"
    >
        <form @submit.prevent="handleSubmit" class="space-y-4">
            <div style="margin-left: 20px; margin-right: 20px;margin-top: 10px;">
                
                <!-- Nom de l'utilisateur -->
                <div class="flex flex-col gap-1">
                    <label class="block font-medium">
                        Prénom et Nom
                    </label>
                    <input
                        v-model="formUser.name"
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
                        Moto/Cylindré
                    </label>
                    <input
                        v-model="formUser.bike"
                        type="text"
                        class="w-full border p-1 rounded"
                        required
                    />
                </div>

                <!-- experience -->
                <div class="flex flex-col gap-1">
                    <label class="block font-medium">
                        Quel est votre expérience à moto?
                    </label>
                    <textarea v-model="formUser.experience" class="w-full border p-1 rounded" rows="4" required></textarea>
                </div>
                
                <!-- But de l'entrainement -->
                <div v-if="isSeance" class="flex flex-col gap-1">
                    <label class="block font-medium">
                        Souhaitez-vous travailler un thème particulier?
                    </label>
                    <textarea v-model="formUser.goal" class="w-full border p-1 rounded" rows="4" required></textarea>
                </div>

            </div>

            <!-- bouton -->
            <div class="flex items-center justify-center " style="margin-bottom:10px;">
                <button
                    type="submit"
                    class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600"
                >
                    Valider l'inscription
                </button>
            </div>
        
        </form>
    </Modal>
</template>

<script>
import inscritAPI from "@/javascript/axios_inscription"
import Modal from "./unitary_elements/modalComponent.vue"

export default {

    props: {
        isSeance: {
            type: Boolean,
            required: true,
        },
        event_id:{
            type: Number,
            required: true
        }
    },

    data() {
        return {
            title: "Inscription",
            isOpen: true,
            formUser: {
                name:"",
                phone:"",
                bike:"",
                email:"",
                experience:"",
                goal:"",
            },
        }
    },

    methods:{
        Cancel() {
            this.$emit('cancelSignal', !this.isOpen)
        },

        async handleSubmit() {
            console.log("Formulaire soumis :", this.formUser)
            await inscritAPI.create_inscrit(this.event_id, this.formUser)
            alert("Inscription enregistrée avec succès !")
            this.$emit('cancelSignal', !this.isOpen)
            
        }
    },

    components: {
        Modal,
    }
}

</script>

<style>

</style>