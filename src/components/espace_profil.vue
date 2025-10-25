<template>
    <div class="flex flex-wrap justify-center gap-2 md:flex-nowrap">
        <button
            v-for="(label, i) in labels"
            :key="i"
            :class="['btn', { pressed: activeIndex === i }]"
            @click="activate(i)"
            @keydown.enter.prevent="activate(i)"
            @keydown.space.prevent="activate(i)"
            :aria-pressed="activeIndex === i ? 'true' : 'false'"
            type="button"
        >
            {{ label }}
        </button>
    </div>
    
    <!-- Contenue de chaque Bouton -->
    <div class="page-container">
        <ProfilInformation
            v-if="activeIndex === 0"
            class="mt-4 w-full"
            :DataUser="user"
            @userChange="e => {user = e}"
        />
        <RessourceGestion
            v-if="activeIndex === 1"
            class="mt-4 w-full"
            :categories="sousCategorie"
        />
    </div>
    
    <!-- Sous-Boutons de chaque Boutons -->
    <div class="flex items-center justify-center" style="margin-top: 20px;">
        <div v-if="activeIndex === 1">
            <button
                @click="() => {isAddingCategories=true;}"
            >{{ "Ajouter une catégorie" }}</button>
        </div>
    </div>

    <!-- Modals -->
    <ModalCategories
        v-if="isAddingCategories"
        :sousCategories="sousCategorie"
        @cancelSignal="(e)=>{isAddingCategories=e;}"
        @newCategorie="(e) => {sousCategorie.push(e); isAddingCategories=!isAddingCategories;}"
    />

</template>

<script>
import ProfilInformation from "./subcomponents/compteGestion.vue"
import RessourceGestion from "./subcomponents/ressourceGestion.vue";
import ModalCategories from "./subcomponents/modal_categories.vue";
import apiSources from "@/javascript/axios_sources"

export default {
    async mounted() {
        const results = await apiSources.get_subcategorie()
        console.log(results.data)
        const results_2 = await apiSources.get_sources()
        console.log(results_2.data)
    },

    data() {
        return {
            labels: ['Mon Profil', 'Ressources', 'Média'],
            activeIndex: 0,
            user: {
                firstName: "Toto",
                lastName: "Tutu",
                email: "test@gmail.com",
                telephone: "0624523551",
                moto: "450",
                groupeSanguin: "A+",
                contactUrgence: "0659875412",
            },
            sousCategorie: [
                "Maitriser l'Embrayage",
            ],
            isAddingCategories: false,
        }
    },

    methods: {
        activate(index) {
            if (this.activeIndex === index) {
                return
            }
            this.activeIndex = index
        }
    },

    components: {
        ProfilInformation,
        RessourceGestion,
        ModalCategories,
    }
}
</script>

<style>
.page-container {
  display: flex;
  flex-direction: column;
  align-items: center; /* centré horizontalement */
  gap: 30px; /* espace vertical entre les deux DepliantWindow */
  margin-top: 40px;
}

.btn-group {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 40px;
}

.btn {
    padding: 20px 20px;
    font-size: 25px;
}
</style>