<template>
    <div>
        <div class="flex flex-wrap w-[95%] mx-auto gap-4 justify-center">
            <button
                @click="Cancel"
                class="flex-1 text-center"
                type="button"
                :style="{
                    display: 'inline-block',
                    color: Couleurs.main_blue,
                    padding: '1.0rem 1.0rem',
                    borderRadius: '9999px',
                    backgroundColor: Couleurs.white,
                    border: `2px solid ${Couleurs.main_blue}`,
                }"
            >
                {{ 'Retour Profil' }}
            </button>
            <button
                class="flex-1 text-center"
                type="button"
                :style="{
                    display: 'inline-block',
                    color: Couleurs.white,
                    padding: '1.0rem 1.0rem',
                    borderRadius: '9999px',
                    backgroundColor: Couleurs.main_blue,
                    border: `2px solid ${Couleurs.main_blue}`,
                }"
            >
                {{ 'Fiche De Suivi' }}
            </button>
        </div>
        <div class="page-container">
            <FicheSuivi
                :user="user"
                :notes="notes"
                :listConseils="listConseil"
                :listExercices="listFavoris"
            />
        </div>
    </div>
</template>

<script>
import FicheSuivi from '@/subcomponents/depliants/fiche_suivi.vue'
import apiFavoris from '@/javascript/api/axios_favoris'
import apiConseils from '@/javascript/api/axios_conseils'
import apiNotes from '@/javascript/api/axios_notes';
import { Couleurs } from '@/javascript/constants/colors';

export default{
    emits:["cancelSignal"],

    props:{
        user:{
            type: Object,
            required: true
        }
    },

    async mounted(){
        const response = await apiFavoris.get_favoris_by_user(this.user.ID)
        if(response?.data?.success){
            this.listFavoris = response.data.favoris ?? []
        }
        const response2 = await apiConseils.get_conseils_by_user(this.user.ID)
        if(response2?.data?.success){
            this.listConseil = response2.data.conseils ?? []

        }
        const response3 = await apiNotes.get_notes_by_user(this.user.ID)
        if (response3?.data?.success){
            this.notes = response3.data.notes ?? []
        }
    },

    data(){
        return {
            Couleurs,
            listConseil:[],
            listFavoris:[],
            notes: [],
        }
    },

    methods:{
        Cancel(){
            this.$emit("cancelSignal")
        }
    },

    components:{
        FicheSuivi
    }
}
</script>

<style>
.page-container {
  display: flex;
  flex-direction: column;
  align-items: center; /* centré horizontalement */
  gap: 2px; /* espace vertical entre les deux DepliantWindow */
  margin-top: 10px;
}
</style>