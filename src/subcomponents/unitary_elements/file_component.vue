<template>
    <div 
        style="padding: 5px;"
        class="flex items-center justify-between"
    >
        <font-awesome-icon
            v-if="canBeFavoris"
            icon="fa-solid fa-star"
            :style="{
                'color': file.isFav ? 'gold' : 'grey'
            }"
            @click="()=>{update_favoris(file)}"
        >
        </font-awesome-icon>
        <label
            class="flex-1 truncate mr-2" :title="file.path_file !== '' ? file.path_file : file.url_file"
        >
            <font-awesome-icon style="margin-right:5px;" icon="fa-solid fa-file-lines"/>
            {{ (file.tag !== "") ? file.tag : file.url_file }}
        </label>
        <div class="flex items-center justify-between gap-2">
            <button
                v-if="canBeUpdated"
                @click="UpdateFile(file)"
                class="appearance-none button-base"
                :style="{
                    '--btn-bg':Couleurs.black,
                    '--btn-hover-bg': Couleurs.black,
                }"
            >
                <font-awesome-icon icon="fa-solid fa-pen-to-square"/>
            </button>
            <button
                v-if="canBeUpdated"
                @click="DeleteFile(file)"
                class="appearance-none button-base"
                :style="{
                    '--btn-bg':Couleurs.main_red,
                    '--btn-hover-bg': Couleurs.dark_red,
                }"
            >
                <font-awesome-icon icon="fa-solid fa-trash"/>
            </button>
            <button
                @click="openUrl(file.url_file, file.path_file)"
                class="appearance-none button-base"
                :style="{
                    '--btn-bg':Couleurs.black,
                    '--btn-hover-bg': Couleurs.black,
                }"
            >
                <font-awesome-icon icon="fa-solid fa-eye"/>
            </button>
            <button
                v-if="file.path_file !== ''"
                @click="DownloadUrl(file)"
                class="appearance-none button-base"
                :style="{
                    '--btn-bg':Couleurs.black,
                    '--btn-hover-bg': Couleurs.black,
                }"
            >
                <font-awesome-icon icon="fa-solid fa-download"/>
            </button>
        </div>
    </div>
</template>

<script>
import api_upload from '@/javascript/api/axios_upload.js'
import apiFavoris from '@/javascript/api/axios_favoris'
import axios_sources from "@/javascript/api/axios_sources.js";
import { Couleurs } from '@/javascript/constants/colors'

export default {
    emits:[
        "updateSubCategoriesAndSources",
        "updateFile"
    ],

    props:{
        file:{
            type: Object,
            required: true
        },
        canBeFavoris:{
            type: Boolean,
            required: false,
            default: false,
        },
        canBeUpdated:{
            type: Boolean,
            required: false,
            default: false,
        }
    },

    data() {
        return {
            Couleurs,
        }
    },

    methods:{
        async update_favoris(file){
            if(file.isFav) {
                const response = await apiFavoris.delete_favoris(file.source_id)
                if(response?.data?.success){
                    file.isFav = false
                }
            }
            else {
                const response = await apiFavoris.add_favoris(file.source_id)
                if(response?.data?.success){
                    file.isFav = true
                }
            }
        },
        
        openUrl(url, file_path) {
            if (url!== "" && url) {
                window.open(url, "_blank");
            }
            else {
                window.open(file_path,"_blank");
            }
        },
        
        async DeleteFile(file) {
            if (confirm("Êtes-vous sûr de vouloir supprimer ce fichier ?")) {
                const response_delete_favoris = await apiFavoris.delete_favoris_from_file(file.source_id)
                if(!response_delete_favoris?.data?.success){
                    console.log("error during the suppression of the favoris")
                }
                const response = await axios_sources.delete_source(file.source_id);
                const wpId = Number(file.id_wp)
                if (wpId > 0) {
                    const res = await api_upload.delete_file(wpId)
                }
                
                if (response.data.success) {
                    this.$emit("updateSubCategoriesAndSources", file)
                } else {
                    alert("Une erreur est survenue lors de la suppression du fichier.");
                }
            }
        },
        
        UpdateFile(file) {
            this.$emit("updateFile", file)
        },

        DownloadUrl(file) {
            const link = document.createElement('a');
            link.href = file.path_file;
            link.download = file.path_file.split('/').pop();
            link.click();
        },
    }
}
</script>