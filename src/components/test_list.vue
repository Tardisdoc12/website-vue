<template>
    <div>{{ "Ceci sert de page test" }}</div>
    <depliantWindow
        :title="'Media'"
        :backgroundColorOpen="Couleurs.dark_blue"
        :writenColorOpen="Couleurs.white"
        :border-color="Couleurs.dark_blue"
        :border-color-open="Couleurs.dark_blue"
        :borderWindowColor="Couleurs.dark_blue"
        :width="'95%'"
        :isOpoenForced="true"
    >
        <depliantWindow
            v-for="(submedias_dir, subkey) in medias"
            :title="submedias_dir.name"
            :key="subkey"
            :width="'100%'"
            :backgroundColor="Couleurs.cyan"
            :borderColorOpen="Couleurs.dark_blue"
            :borderColor="Couleurs.dark_blue"
            :backgroundColorOpen="Couleurs.cyan"
            :writenColor="Couleurs.main_blue"
            :writenColorOpen="Couleurs.main_blue"
            :showBorder="false"
            :isOpoenForced="true"
            :borderRadius="'0px'"
        >
            <MediaViewer
                :medias="submedias_dir.files"
                :loading="loading"
                :error="error"
                @media-clicked="handleMediaClick"
            />
        </depliantWindow>
    </depliantWindow>
    <div class="test-controls">
        <button @click="addPlaceholders" :disabled="loading">
            + Ajouter des placeholders
        </button>
        <button @click="simulateLoading" :disabled="loading">
            ⟳ Simuler chargement
        </button>
    </div>
</template>

<script>
import MediaViewer from "@/subcomponents/unitary_elements/media_viewer.vue";
import depliantWindow from "@/subcomponents/unitary_elements/depliantWindow.vue";
import { Couleurs } from "@/javascript/constants/colors";

export default{

    mounted(){
        this.loading = true;
        let aftermedia = {}
        let initialmedia = [
            { id: 1, thumbnails_100: "https://picsum.photos/seed/1/100/100", name: "Media 1", type:"image/jpeg", parent_id: 7},
            { id: 2, thumbnails_100: "https://picsum.photos/seed/2/100/100", name: "Media 2", type:"image/jpeg", parent_id: 7},
            { id: 3, thumbnails_100: null, name: "Media 3", type:"image/jpeg", parent_id: 7}, // Placeholder
            { id: 4, thumbnails_100: "https://picsum.photos/seed/4/100/100", name: "Media 4", type:"image/jpeg", parent_id: 7},
            { id: 5, thumbnails_100: null, name: "Media 5", type:"image/jpeg", parent_id: 7}, // Placeholder
            { id: 6, thumbnails_100: "https://picsum.photos/seed/6/100/100", name: "Media 6", type:"image/jpeg", parent_id: 7},
            { id: 7, thumbnails_100: null, name: "Directory 1", type: "directory"},
        ]

        initialmedia.filter(media => media.type === "directory").forEach(
            media => {
                aftermedia[media.id] = {
                    name: media.name,
                    files: []
                }
            }
        )

        initialmedia.forEach(
            media => {

                if (media.type !== "directory"){
                    aftermedia[media.parent_id]["files"].push(media)
                }
            }
        )

        this.medias = aftermedia
        this.loading = false
    },

    data() {
        return {
            Couleurs,
            loading: false,
            error: null,
            medias: [],
            nextId: 1,
        };
    },

    components:{
        MediaViewer,
        depliantWindow
    },
    methods: {
        // Ajoute 6 placeholders immédiatement
        addPlaceholders() {
            const batch = Array.from({ length: 6 }, (_, i) => ({
                id: this.nextId + i,
                thumbnails_100: null,   // null → affiche le fallback ✕
                name: `Media ${this.nextId + i}`,
            }));
            this.medias[7]["files"].push(...batch);
            this.nextId += 6;
        },

        // Simule un appel API : spinner 2s puis ajoute les médias
        simulateLoading() {
            this.loading = true;
            this.error   = null;

            setTimeout(() => {
                const batch = Array.from({ length: 6 }, (_, i) => ({
                    id: this.nextId + i,
                    thumbnails_100: `https://picsum.photos/seed/${this.nextId + i}/100/100`,
                    name: `Media ${this.nextId + i}`,
                    type: "image/jpeg",
                    parent_id: 7,
                }));
                this.medias[7]["files"].push(...batch);
                this.nextId  += 6;
                this.loading  = false;
            }, 2000);
        },

        handleMediaClick(media) {
            console.log("Media clicked:", media);
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
