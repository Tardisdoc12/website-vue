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
            :subCategoriesAndSources="structuresRessources"
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
        :subCategoriesAndSources="structuresRessources"
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
        const results_2 = await apiSources.get_sources()
        console.log(results_2.data)
        const data = results_2.data

        data.forEach(item => {
            const { categorie_id, subcat_id, source_id, subcat_title, path_file, url_file, tag } = item;
            console.log(tag)
            // Si la catégorie n’existe pas encore, on la crée
            // if (!this.structuresRessources[categorie_id]) {
            //     return
            // }

            // Si la sous-catégorie n’existe pas encore, on la crée
            if (!this.structuresRessources[categorie_id]["subcats"][subcat_id]) {
                    this.structuresRessources[categorie_id]["subcats"][subcat_id] = {
                    subcat_title,
                    files: [],
                };
            }

            // On ajoute le fichier à la liste si il existe
            if ((path_file && path_file.trim() !== "") || (url_file && url_file.trim() !== "")){
                this.structuresRessources[categorie_id]["subcats"][subcat_id].files.push({ path_file, url_file, source_id, tag });
            }
        });
        console.log(this.structuresRessources)
    },

    data() {
        const RessourcesCategories= [
            "Feuille de Suivi",
            "Fiches et Exercices",
            "Parcours d'entrainement",
            "Liens Utiles"
        ]
        return {
            labels: ['Mon Profil', 'Ressources', 'Média'],
            
            structuresRessources: {
                1:{"categorie_title":RessourcesCategories[1],"subcats":{}},
                2:{"categorie_title":RessourcesCategories[2],"subcats":{}},
                3:{"categorie_title":RessourcesCategories[3],"subcats":{}},
            },
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