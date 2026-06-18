<template>
    <div>{{ "Ceci sert de page test" }}</div>
    <depliantWindow
        v-if="root.files.length > 0 || root.directories.length > 0"
        title="Media"
        :backgroundColorOpen="Couleurs.dark_blue"
        :writenColorOpen="Couleurs.white"
        :border-color="Couleurs.dark_blue"
        :border-color-open="Couleurs.dark_blue"
        :borderWindowColor="Couleurs.dark_blue"
        :width="'95%'"
        :isOpoenForced="true"
    >
        <!-- fichiers de la racine -->
        <MediaViewer
            v-if="root.files.length > 0"
            :medias="root.files"
            :loading="loading"
            @media-clicked="handleMediaClick"
        />

        <!-- dossiers de la racine -->
        <MediaDirectory
            v-for="dir in root.directories"
            :key="dir.id"
            :directory="dir"
            @media-clicked="handleMediaClick"
        />
    </depliantWindow>

    <Modal 
        v-if="isOpenModal"
        :title="mediaSelected.name"
        @changeBool="Cancel"
        :width="'80%'"
    >
        <div style="display: flex; gap: 1rem; padding: 1rem;">
            <!-- Image -->
            <div style="flex: 1;">
                <img 
                    :src="mediaSelected.thumbnail_300" 
                    alt="Media"
                    style="width: 100%; height: auto; display: block; border-radius: 4px;"
                />
            </div>

            <!-- Infos -->
            <div style="flex: 1; display: flex; flex-direction: column; gap: 0.5rem;">
                <p><strong>Nom :</strong> {{ mediaSelected.name }}</p>
                <p><strong>Type :</strong> {{ mediaSelected.type }}</p>
                <p><strong>Taille :</strong> {{ mediaSelected.size }}</p>
                <p><strong>Modifié le :</strong> {{ formatDate(mediaSelected.last_modified_at) }}</p>
            </div>
        </div>
    </Modal>
</template>

<script>
import MediaViewer from "@/subcomponents/unitary_elements/media_viewer.vue";
import depliantWindow from "@/subcomponents/unitary_elements/depliantWindow.vue";
import MediaDirectory from "@/subcomponents/depliants/media_depliant_directory.vue";
import { Couleurs } from "@/javascript/constants/colors";
import apiMedia from "@/javascript/api/api_media.js";
import Modal from "@/subcomponents/unitary_elements/modalComponent.vue";
export default{

    async mounted(){
        this.loading = true;
        const result = await apiMedia.get_children_directory(0);
        const response = result.data;
        if (response.success) {
            this.root.files = Object.values(response.medias).filter(media => media.type !== "dir");
            this.root.directories = Object.values(response.medias).filter(media => media.type === "dir");
        }
        this.loading = false;
    },

    data() {
        return {
            Couleurs,
            loading: false,
            error: null,
            mediaSelected: null,
            isOpenModal: false,
            root: {
                files: [],
                directories: []
            },
        };
    },

    components:{
        MediaViewer,
        depliantWindow,
        MediaDirectory,
        Modal
    },
    methods: {
        formatDate(timestamp){
            return new Date(timestamp * 1000).toLocaleDateString('fr-FR', {
                day: '2-digit',
                month: 'long',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            })
        },
        Cancel() {
            this.isOpenModal = false
            this.mediaSelected = null
        },
        handleMediaClick(media) {
            console.log("Media clicked:", media);
            this.mediaSelected = media;
            this.isOpenModal = true;
        },
    },
}
</script>

<style scoped>
.test-controls {
    display: flex;
    gap: 10px;
    margin-bottom: 12px;
}

.test-controls button {
    padding: 8px 16px;
    border: 1px solid #ccc;
    border-radius: 6px;
    background: #f5f5f5;
    cursor: pointer;
    font-size: 13px;
    transition: background 0.15s;
}

.test-controls button:hover:not(:disabled) {
    background: #e8e8e8;
}

.test-controls button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>
