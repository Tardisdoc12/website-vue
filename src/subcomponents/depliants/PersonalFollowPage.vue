<template>
    <div>
        <FicheSuivi
            :user="user"
            :listConseils="listConseil"
            :listExercices="listFavoris"
        />
        <div class="page-container">
            <button
                @click="Cancel"
                class="appearance-none button-base"
            >
                {{ "Retour à mon profil" }}
            </button>
        </div>
    </div>
</template>

<script>
import FicheSuivi from '@/subcomponents/depliants/fiche_suivi.vue'
import apiFavoris from '@/javascript/api/axios_favoris'
import apiConseils from '@/javascript/api/axios_conseils'

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
    },

    data(){
        return {
            listConseil:[],
            listFavoris:[]
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