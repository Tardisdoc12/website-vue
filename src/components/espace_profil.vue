<template>
    <div v-if="!isInMyFollowPage && !isInOtherFollowPage">
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
        
        <!-- Contenue de chaque Bouton -->
        <div class="page-container">
            <ProfilInformation
                v-if="activeIndex === 0"
                :DataUser="user"
                @userChange="e => {user = e}"
            />
            <RessourceGestion
                v-if="activeIndex === 1 && !isNonAdherent"
                :subCategoriesAndSources="structuresRessources"
                :isBureau="isBureau"
                @updateSubCategoriesAndSources="updateSubCategoriesAndSources"
            />
            <div
                v-else-if="activeIndex === 1 && isNonAdherent"
            >
                <p class="flex-1 text-center"> Les Ressources ne sont disponibles que pour les personnes de l'Associations.²</p>
            </div>
            <MediaSpace
                v-if="activeIndex === 2"
            />
        </div>
        
        <!-- Sous-Boutons de chaque Boutons -->
        <div class="flex items-center justify-center gap-4" style="margin-top: 20px;" v-if="activeIndex === 1 && isBureau">
            <button
                class="appearance-none button-base"
                @click="() => {isAddingCategories=true;}"
            >{{ "Ajouter une catégorie" }}</button>
            <button
                class="appearance-none button-base"
                @click="() => {isAddingFiles=true;}"
            >{{ "Ajouter un fichier" }}</button>
            <button
                @click="() => {isRemovingCategories=true;}"
                class="appearance-none button-base"
            >{{ "Supprimer une Catégorie" }}</button>
        </div>

        <div class="flex items-center justify-center gap-4" style="margin-top: 20px;" v-if="activeIndex === 0">
            <button
                class="appearance-none button-base"
                @click="() => {isInMyFollowPage=true;}"
            >
                {{ "Accéder à ma fiche de suivi" }}
            </button>
            <button
                v-if="isEncadrantComp"
                class="appearance-none button-base"
                @click="()=>{showListMembers = true}"
            >
                {{ "Liste des Membres" }}
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
        <ModalSearch
            v-if="showListMembers"
            :ColumnList="listColumn"
            :listToPass="listMembers"
            :title="'Membres:'"
            @cancel-signal="()=>{showListMembers=false}"
            @select="selectUser"
        />
    </div>
    <div
        v-else-if="!isInMyFollowPage && isInOtherFollowPage"
    >
        <EspaceAdherent
            :user="otherUser"
            @cancel-signal="CancelFollowPage"
        />
    </div>
    <div v-else>
        <FicheSuivi
            :user="user"
        />
        <div class="page-container">
            <button
                @click="()=>{isInMyFollowPage = false}"
                class="appearance-none button-base"
            >
                {{ "Retour à mon profil" }}
            </button>
        </div>
    </div>
</template>

<script>
import ProfilInformation from "@/subcomponents/depliants/compteGestion.vue"
import RessourceGestion from "@/subcomponents/depliants/ressourceGestion.vue";
import ModalCategories from "@/subcomponents/modals/modal_categories.vue";
import ModalSearch from "@/subcomponents/modals/modal_search.vue"
import ModalFilesAccount from "@/subcomponents/modals/modal_files_account.vue";
import ModalRemoveSubcategorie from "@/subcomponents/modals/modal_remove_subcategorie.vue";
import MediaSpace from "@/subcomponents/media_space.vue";
import FicheSuivi from "@/subcomponents/depliants/fiche_suivi.vue"
import apiSources from "@/javascript/api/axios_sources"
import apiEvents from "@/javascript/api/axios_events"
import { jwtDecode } from "jwt-decode"
import api from "@/javascript/api/users_wp.js"
import { Couleurs } from "@/javascript/constants/colors.js"
import { isEncadrant } from "@/javascript/constants/roles";
import EspaceAdherent from "@/subcomponents/depliants/adherent_page.vue"

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
            this.user.events = []
            const events = await apiEvents.getEventUser(this.user.ID,this.user.email)
            
            for(const element of events.results){
                const event = await apiEvents.getEvent(element.event_id)
                this.user.events.push(event)
            }
            if (this.user.roles.includes("administrator") || this.user.roles.includes("bureau")) {
                this.isBureau = true
            }
            if (isEncadrant(this.user.roles)){
                const usersMembers = await api.get_adherents()
                if (usersMembers?.data?.users){
                    this.listMembers = usersMembers.data.users.filter(el => Number(el.ID) !== 1)
                }
            }
        }

    },

    watch: {
        structuresRessources: {
            handler() {},
            deep: true
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
            
            Couleurs,
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
            showListMembers: false,
            isInMyFollowPage: false,
            isInOtherFollowPage:false,
            isBureau: false,
            listColumn: {
                "firstName":"Prénom",
                "lastName":"Nom",
            },
            listMembers: [],
            otherUser: null,
        }
    },

    computed:{
        isEncadrantComp(){
            return isEncadrant(this.user.roles)
        },

        isNonAdherent() {
            return this.user?.roles?.includes("non_adherent") || false;
        },
    },

    methods: {
        updateSubCategoriesAndSources(e){
            this.structuresRessources = { ...e}
        },
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
        },
        selectUser(user) {
            this.showListMembers = false
            this.isInOtherFollowPage = true
            this.otherUser = user
        },
        CancelFollowPage(){
            this.otherUser = null
            this.isInOtherFollowPage = false
        }
    },

    components: {
        ProfilInformation,
        RessourceGestion,
        ModalFilesAccount,
        ModalCategories,
        ModalRemoveSubcategorie,
        MediaSpace,
        ModalSearch,
        FicheSuivi,
        EspaceAdherent,
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