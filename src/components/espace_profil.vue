<template>
    <div v-if="!isInMyFollowPage">
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
                    display: inline-block,
                    color: activeIndex === i ? '#FFFFFF' : '#245473',
                    padding: '1.0rem 1.0rem',
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
                :isBureau="isBureau"
                @updateSubCategoriesAndSources="(e) => {structuresRessources = {...e}}"
            />
            <MediaSpace
                v-if="activeIndex === 2"
                class="mt-4 w-full"
            />
        </div>
        
        <!-- Sous-Boutons de chaque Boutons -->
        <div class="flex items-center justify-center gap-4" style="margin-top: 20px;" v-if="activeIndex === 1 && isBureau">
            <button
                class="appearance-none"
                :style="{
                    display: 'inline-block',
                    color: 'white',
                    padding: '0.85rem 1rem',
                    borderRadius: '0.375rem',
                    backgroundColor: '#245473',
                }"
                @click="() => {isAddingCategories=true;}"
            >{{ "Ajouter une catégorie" }}</button>
            <button
                class="appearance-none"
                :style="{
                    display: 'inline-block',
                    color: 'white',
                    padding: '0.85rem 1rem',
                    borderRadius: '0.375rem',
                    backgroundColor: '#245473',
                }"
                @click="() => {isAddingFiles=true;}"
            >{{ "Ajouter un fichier" }}</button>
            <button
                @click="() => {isRemovingCategories=true;}"
                class="appearance-none"
                :style="{
                    display: 'inline-block',
                    color: 'white',
                    padding: '0.85rem 1rem',
                    borderRadius: '0.375rem',
                    backgroundColor: '#245473',
                }"
            >{{ "Supprimer une Catégorie" }}</button>
        </div>

        <div class="flex items-center justify-center gap-4" style="margin-top: 20px;" v-if="activeIndex === 0">
            <button
                class="appearance-none"
                :style="{
                    display: 'inline-block',
                    color: 'white',
                    padding: '0.85rem 1rem',
                    borderRadius: '0.375rem',
                    backgroundColor: '#245473',
                }"
                @click="() => {isInMyFollowPage=true;}"
            >
                {{ "Accéder à ma fiche de suivi" }}
            </button>
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
    </div>
    <div v-else>
        <h1 class="text-2xl font-bold mb-4">Ma fiche de suivi</h1>
        <p>Cette page est en cours de développement. Elle permettra d'afficher les informations de suivi de l'utilisateur, telles que les progrès réalisés, les objectifs atteints, et d'autres données pertinentes pour le suivi de sa progression.</p>
        <button
            @click="() => {isInMyFollowPage=false;}"
            class="mt-4 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
        >
            {{ "Retour à mon profil" }}
        </button>
    </div>
</template>

<script>
import ProfilInformation from "./subcomponents/compteGestion.vue"
import RessourceGestion from "./subcomponents/ressourceGestion.vue";
import ModalCategories from "./subcomponents/modal_categories.vue";
import ModalFilesAccount from "./subcomponents/modal_files_account.vue";
import ModalRemoveSubcategorie from "./subcomponents/modal_remove_subcategorie.vue";
import MediaSpace from "./subcomponents/media_space.vue";
import apiSources from "@/javascript/api/axios_sources"
import { jwtDecode } from "jwt-decode"
import api from "../javascript/api/users_wp.js"

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
        // On recherche qui est l'utilisateur connecté pour afficher les bonnes informations
        const token = sessionStorage.getItem("mps_moto")
        if (token) {
            const decoded = jwtDecode(token)
            const user_id = decoded.data.user.id
            const user_info = await api.get_user(user_id)
            this.user = user_info.user
            if (this.user.roles.includes("administrator") || this.user.roles.includes("bureau")) {
                this.isBureau = true
            }
        }

    },

    watch: {
        structuresRessources: {
            deep: true,
        }
    },

    data() {
        const RessourcesCategories= [
            "Fiches et Exercices",
            "Parcours et Tracés",
            "Liens et Documents Utiles",
            "Gestion",
        ]
        return {
            labels: ['Profil', 'Ressources', 'Médias'],
            
            structuresRessources: {
                0:{"categorie_title":RessourcesCategories[0],"subcats":{}},
                1:{"categorie_title":RessourcesCategories[1],"subcats":{}},
                2:{"categorie_title":RessourcesCategories[2],"subcats":{}},
                3:{"categorie_title":RessourcesCategories[3],"subcats":{}},
            },
            activeIndex: 0,
            user: {},
            isAddingFiles: false,
            isAddingCategories: false,
            isRemovingCategories: false,
            isInMyFollowPage: false,
            isBureau: false,
        }
    },

    methods: {
        handleNewCategorie(newCategorie) {

            const cat = this.structuresRessources[newCategorie.id_categorie]

            cat.subcats = {
                ...cat.subcats,
                [newCategorie.id]: {
                subcat_title: newCategorie.title,
                files: []
                }
            }
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
            const { id_categorie, id_subcategorie } = newFile;
            const subcat = this.structuresRessources[id_categorie]?.subcats[id_subcategorie];
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
        MediaSpace,
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