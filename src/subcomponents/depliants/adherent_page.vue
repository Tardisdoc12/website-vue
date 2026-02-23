<template>
    <h1>{{ "Compte d'autre utilisateur" }}</h1>
    
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
            @cancelSignal="CancelFollowPage"
        />
    </div>
    <div class="page-container">
        <button
            @click="CancelFollowPage"
            class="appearance-none button-base"
        >
            {{ "Retour à mon profil" }}
        </button>
    </div>

</template>

<script>
import EspaceProfil from "@/subcomponents/depliants/compteGestion.vue"
import FicheSuivi from "@/subcomponents/depliants/fiche_suivi.vue"
import { Couleurs } from "@/javascript/constants/colors";

export default {
    emits:[
        "cancelSignal"
    ],

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
    },

    components:{
        EspaceProfil,
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