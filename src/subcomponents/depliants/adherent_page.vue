<template>
    <h1>{{ "Compte de " + `${user.firstName} ${user.lastName}` }}</h1>
    
    <div class="flex flex-wrap w-[95%] mx-auto gap-4 justify-center">
        <button
            v-for="(label, i) in labels"
            :key="i"
            :class="['btn', { pressed: activeIndex === i }]"
            class="flex-1 text-center"
            @click="activate(i)"
            @keydown.enter.prevent="activate(i)"
            @keydown.space.prevent="activate(i)"
            :aria-pressed="activeIndex === i ? 'true' : 'false'"
            type="button"
            :style="{
                display: 'inline-block',
                color: activeIndex === i ? Couleurs.white : Couleurs.main_blue,
                padding: '1.0rem 1.0rem',
                borderRadius: '9999px',
                backgroundColor: activeIndex === i ? Couleurs.main_blue : Couleurs.white,
                border: `2px solid ${Couleurs.main_blue}`,
            }"
        >
            {{ label }}
        </button>
    </div>
    <div class="page-container">
        <EspaceProfil
            v-if="activeIndex === 0"
            :DataUser="user"
            :canUpdate="false"
        />
        <FicheSuivi
            v-if="activeIndex === 1"
            :user="user"
            :isMe="false"
            :listConseils="listConseil"
            :listExercices="listFavoris"
            @cancelSignal="CancelFollowPage"
        />
    </div>
    <div
        class="flex flex-wrap mx-auto gap-2 justify-center"
        style="margin-top: 10px;"
    >
        <button
            @click="CancelFollowPage"
            class="appearance-none button-base"
        >
            {{ "Retour à mon profil" }}
        </button>

        <button
            v-if="activeIndex === 1"
            @click="startAddConseil"
            class="appearance-none button-base"
        >
            {{ 'Ajouter un exercice' }}
        </button>

        <button
            v-if="activeIndex === 1"
            @click="startSuppression"
            class="appearance-none button-base"
        >
            {{ 'Supprimer un exercice' }}
        </button>
    </div>

    <ModalExercises
        v-if="isStartingAddConseil"
        :title="'Ajouter un exercice'"
        :list="listExerciseToChoose"
        @cancelSignal="()=>{isStartingAddConseil = false}"
        @add="AddExercice"
    />

</template>

<script>
import EspaceProfil from "@/subcomponents/depliants/compteGestion.vue"
import FicheSuivi from "@/subcomponents/depliants/fiche_suivi.vue"
import { Couleurs } from "@/javascript/constants/colors";
import ModalExercises from "../modals/modal_exercises.vue";
import apiSources from '@/javascript/api/axios_sources'
import apiConseil from '@/javascript/api/axios_conseils'
import apiFavoris from '@/javascript/api/axios_favoris'

export default {
    emits:[
        "cancelSignal"
    ],

    async mounted() {
        const response = await apiSources.get_exercices()
        if(response?.data?.success){
            this.listExerciseToChoose = response.data.exercices
        }
        const response3 = await apiFavoris.get_favoris_by_user(this.user.ID)
        if(response3?.data?.success){
            this.listFavoris = response3.data.favoris ?? []
        }
        const response2 = await apiConseil.get_conseils_by_user(this.user.ID)
        if(response2?.data?.success){
            this.listConseil = response2.data.conseils ?? []

        }
    },

    props:{
        user:{
            type:Object,
            required: true
        }
    },

    data() {
        return {
            Couleurs,
            labels: ['Profil', 'Fiche de suivi'],
            activeIndex: 0,
            isStartingAddConseil: false,
            listExerciseToChoose: [],
            listFavoris:[],
            listConseil:[]
        }
    },

    methods:{
        CancelFollowPage() {
            this.$emit("cancelSignal")
        },
        
        activate(index) {
            if (this.activeIndex === index) {
                return
            }
            this.activeIndex = index
        },
        
        startAddConseil(){
            console.log("Ajouter un exercice à conseillé")
            this.isStartingAddConseil = true
        },

        async AddExercice(exercice){
            const response = await apiConseil.add_conseils(exercice.source_id, this.user.ID)
            if(response?.data?.success){
                this.listConseil.push(exercice)
            }
        },

        startSuppression(){
            console.log("Supprimer un exercice")
        }  
    },

    components:{
        EspaceProfil,
        FicheSuivi,
        ModalExercises
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