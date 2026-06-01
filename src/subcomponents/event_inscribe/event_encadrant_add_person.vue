<template>
    <div style="margin-left: 5px; margin-right: 5px;">
        <!-- Étape 1 : On demande si la personne a un compte -->
        <div class="flex flex-col gap-1">
            <label class="block font-medium">Ce participant a-t-il un compte ? <span style="color:red">*</span></label>
            <div style="display:flex; gap:10px;">
                <button
                    type="button"
                    class="toggle-btn"
                    :class="{ 'toggle-btn--active': participant.hasAccount === true }"
                    :style="{
                        color: participant.hasAccount === true ? Couleurs.white : Couleurs.main_blue,
                        backgroundColor: participant.hasAccount === true ? Couleurs.main_blue : Couleurs.white,
                        border: `2px solid ${Couleurs.main_blue}`,
                    }"
                    @click="setHasAccount(true)"
                >Oui</button>
                <button
                    type="button"
                    class="toggle-btn"
                    :class="{ 'toggle-btn--active': participant.hasAccount === false }"
                    :style="{
                        color: participant.hasAccount === false ? Couleurs.white : Couleurs.main_blue,
                        backgroundColor: participant.hasAccount === false ? Couleurs.main_blue : Couleurs.white,
                        border: `2px solid ${Couleurs.main_blue}`,
                    }"
                    @click="setHasAccount(false)"
                >Non</button>
            </div>
        </div>

        <!-- Étape 2 : Si oui, on affiche le champ de recherche -->
        <!-- 2a. OUI → recherche du compte -->
        <template v-if="participant.hasAccount === true">
            <SearchComponent
                :list="listMembers"
                :ColumnToShow="listColumn"
                @select="searchParticipant"
            />

            <!-- Compte trouvé -->
            <div v-if="participant.searchResult === 'found'" class="found-card">
                ✓ {{ participant.name }} — {{ participant.bike }}
            </div>
            <!-- Compte non trouvé malgré recherche -->
            <p v-if="participant.searchResult === 'not_found'" class="not-found-msg">
                Aucun compte trouvé pour cette recherche.
            </p>

            <!-- Expérience + goal après compte trouvé -->
            <template v-if="participant.searchResult === 'found'">
                <div class="flex flex-col gap-1" v-if="!isAdherentParticipant()">
                    <label class="block font-medium">Expérience à moto <span style="color:red">*</span></label>
                    <textarea v-model="participant.experience" class="w-full border p-1 rounded" rows="3"></textarea>
                </div>
                <div v-if="isSeance" class="flex flex-col gap-1">
                    <label class="block font-medium">Thème particulier?</label>
                    <textarea v-model="participant.goal" class="w-full border p-1 rounded" rows="2"></textarea>
                </div>
            </template>
        </template>

        <!-- 2b. NON → formulaire classique -->
        <template v-if="participant.hasAccount === false">
            <div class="flex flex-col gap-1">
                <label class="block font-medium">Prénom et Nom <span style="color:red">*</span></label>
                <input v-model="participant.name" type="text" class="w-full border p-1 rounded" />
            </div>
            <div class="flex flex-col gap-1">
                <label class="block font-medium">E-mail <span style="color:red">*</span></label>
                <input v-model="participant.email" type="email" class="w-full border p-1 rounded" />
            </div>
            <div class="flex flex-col gap-1">
                <label class="block font-medium">Téléphone <span style="color:red">*</span></label>
                <input v-model="participant.phone" type="tel" class="w-full border p-1 rounded" pattern="[0-9]{10}" />
            </div>
            <div class="flex flex-col gap-1">
                <label class="block font-medium">Moto/Cylindré <span style="color:red">*</span></label>
                <input v-model="participant.bike" type="text" class="w-full border p-1 rounded" />
            </div>
            <div class="flex flex-col gap-1">
                <label class="block font-medium">Expérience à moto <span style="color:red">*</span></label>
                <textarea v-model="participant.experience" class="w-full border p-1 rounded" rows="3"></textarea>
            </div>
            <div v-if="isSeance" class="flex flex-col gap-1">
                <label class="block font-medium">Thème particulier?</label>
                <textarea v-model="participant.goal" class="w-full border p-1 rounded" rows="2"></textarea>
            </div>
        </template>

        <div style="margin: 16px 20px 10px; display:flex; flex-direction:column; gap:8px;">

            <button
                type="button"
                class="button-base"
                :disabled="isSubmitting"
                @click="handleSubmit"
            >
                {{ isSubmitting ? 'Inscription...' : 'Confirmer l\'inscription' }}
            </button>
        </div>
    </div>
</template>

<script>
import inscritAPI from "@/javascript/api/axios_inscription"
import SearchComponent from '@/subcomponents/unitary_elements/search_component.vue';
import { Events } from "@/javascript/constants/events_type"
import { Couleurs } from "@/javascript/constants/colors.js"
import api from "@/javascript/api/users_wp.js"

export default{

    emits: ['inscrit'],

    props: {
        event: {
            type: Object,
            required: true
        },
    },

    async mounted() {
        const usersMembers = await api.get_adherents()
        
        this.listMembers = usersMembers.data.users
    },

    data() {
        return {
            participant: {
                hasAccount: false,
                searchQuery: "",
                searchResult: null,
                name: "",
                email: "",
                phone: "",
                bike: "",
                experience: "",
                goal: ""
            },
            isSubmitting: false,
            Couleurs,
            listColumn: {
                "firstName":"Prénom",
                "lastName":"Nom",
            },
            listMembers: [],
        }
    },
    
    computed: {
        isSeance() {
            return this.event.categorie === Events.seance
        },
    },

    methods: {
        setHasAccount(value) {
            // Reset les champs liés si on change d'avis
            this.participant.hasAccount = value
            this.participant.searchQuery = ""
            this.participant.searchResult = null
            this.participant.name = ""
            this.participant.email = ""
            this.participant.phone = ""
            this.participant.bike = ""
            this.participant.experience = ""
            this.participant.goal = ""
        },

        searchParticipant(element) {
            try {
                console.log("Element sélectionné dans SearchComponent :", element)
                if (element && element?.ID) {
                    const u = element
                    this.participant = {
                        ...this.participant,
                        name:  `${u.firstName} ${u.lastName}`,
                        email: u.email,
                        phone: u.telephone,
                        bike:  u.moto,
                        roles: u.roles ?? ["non_adherent"],
                        searchResult: 'found'
                    }
                } else {
                    this.participant.searchResult = 'not_found'
                }
            } catch {
                this.participant.searchResult = 'not_found'
            }
        },

        isAdherentParticipant() {
            return this.participant?.roles ? !this.participant.roles.includes("non_adherent") : false
        },

        async handleSubmit() {
            this.isSubmitting = true
            try {
                const res = await inscritAPI.create_inscrit(this.event.event_id, this.participant)
                if (!res?.data?.success) throw new Error("Échec pour " + this.participant.name)
                this.$emit('inscrit')
            } catch (err) {
                console.error("❌ Erreur inscription:", err)
            } finally {
                this.isSubmitting = false
            }
        },
    },

    components: {
        SearchComponent
    }
}

</script>

<style>
</style>