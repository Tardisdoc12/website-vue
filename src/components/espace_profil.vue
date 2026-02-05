<template>
    <div class="flex flex-wrap w-[80%] mx-auto gap-4 justify-center">
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
                display: inline-block,
                color: activeIndex === i ? '#FFFFFF' : '#245473',
                padding: '1.5rem 1.5rem',
                borderRadius: '9999px',
                backgroundColor: activeIndex === i ? '#245473' : '#FFFFFF',
                border: '2px solid #245473',
            }"
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
    <div class="flex items-center justify-center gap-4" style="margin-top: 20px;" v-if="activeIndex === 1">
        <button
            @click="() => {isAddingCategories=true;}"
        >{{ "Ajouter une catégorie" }}</button>
        <button
            @click="() => {isAddingFiles=true;}"
        >{{ "Ajouter un fichier" }}</button>
        <button
            @click="() => {isRemovingCategories=true;}"
            class="appearance-none"
            :style="{
                display: 'inline-block',
                color: 'white',
                padding: '1rem 1rem',
                borderRadius: '0.375rem',
                backgroundColor: '#FF0000',
            }"
        >{{ "Supprimer une Catégorie" }}</button>
    </div>

    <!-- Modals -->
    <ModalCategories
        v-if="isAddingCategories"
        :subCategoriesAndSources="structuresRessources"
        @cancelSignal="(e)=>{isAddingCategories=e;}"
        @newCategorie="handleNewCategorie"
    />

    <ModalFilesAccount
        v-if="isAddingFiles"
        :subCategoriesAndSources="structuresRessources"
        @cancelSignal="(e)=>{isAddingFiles=e;}"
        @newFile="handleNewFile"
    />

    <ModalRemoveSubcategorie
        v-if="isRemovingCategories"
        :subCategoriesAndSources="structuresRessources"
        @cancelSignal="(e)=>{isRemovingCategories=e;}"
        @deleteSubcategorie="handleDeleteSubcategorie"
     />

</template>

<script>
import ProfilInformation from "./subcomponents/compteGestion.vue"
import RessourceGestion from "./subcomponents/ressourceGestion.vue";
import ModalCategories from "./subcomponents/modal_categories.vue";
import ModalFilesAccount from "./subcomponents/modal_files_account.vue";
import ModalRemoveSubcategorie from "./subcomponents/modal_remove_subcategorie.vue";
import apiSources from "@/javascript/axios_sources"

export default {
    async mounted() {
        const results_2 = await apiSources.get_sources()
        const data = results_2.data

        data.forEach(item => {
            const { categorie_id, subcat_id, source_id, subcat_title, path_file, url_file, tag, id_wp } = item;
            // Si la sous-catégorie n’existe pas encore, on la crée
            if (!this.structuresRessources[categorie_id]["subcats"][subcat_id]) {
                    this.structuresRessources[categorie_id]["subcats"][subcat_id] = {
                    subcat_title,
                    files: [],
                };
            }

            // On ajoute le fichier à la liste si il existe
            if ((path_file && path_file.trim() !== "") || (url_file && url_file.trim() !== "")){
                this.structuresRessources[categorie_id]["subcats"][subcat_id].files.push({ path_file, url_file, source_id, tag, id_wp });
            }
        });
    },

    data() {
        const RessourcesCategories= [
            "Fiches et Exercices",
            "Parcours d'entrainement",
            "Liens Utiles"
        ]
        return {
            labels: ['Mon Profil', 'Ressources', 'Média'],
            
            structuresRessources: {
                0:{"categorie_title":RessourcesCategories[0],"subcats":{}},
                1:{"categorie_title":RessourcesCategories[1],"subcats":{}},
                2:{"categorie_title":RessourcesCategories[2],"subcats":{}},
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
            isAddingFiles: false,
            isAddingCategories: false,
            isRemovingCategories: false,
        }
    },

    methods: {
        handleNewCategorie(newCategorie) {
            let cat = this.structuresRessources[newCategorie.id_categorie]
            
            cat.subcats = {
                ...cat.subcats,
                [newCategorie.id]: {
                subcat_title: newCategorie.title,
                files: []
                }
            };
        },
        handleDeleteSubcategorie(idSubcategorie) {
            for (const catKey in this.structuresRessources) {
                const cat = this.structuresRessources[catKey];
                if (cat.subcats[idSubcategorie]) {
                    delete cat.subcats[idSubcategorie];
                    break;
                }
            }
        },
        handleNewFile(newFile) {
            const { id_categorie, id_subcat } = newFile;
            const subcat = this.structuresRessources[id_categorie]?.subcats[id_subcat];

            if (!subcat) return;

            subcat.files = [...subcat.files, newFile];
        },
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
        ModalFilesAccount,
        ModalCategories,
        ModalRemoveSubcategorie,
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