<template>
    <depliantWindow
        :title="directory.name"
        :width="'100%'"
        :isOpoenForced="false"
        :backgroundColor="Couleurs.cyan"
        :borderColorOpen="Couleurs.dark_blue"
        :borderColor="Couleurs.dark_blue"
        :backgroundColorOpen="Couleurs.cyan"
        :writenColor="Couleurs.main_blue"
        :writenColorOpen="Couleurs.main_blue"
        :showBorder="false"
        :borderRadius="'0px'"
        @open="loadDirectory"
    >
        <MediaViewer
            v-if="content.files.length > 0"
            :medias="content.files"
            :loading="loading"
            :error="error"
            @media-clicked="mediaClicked"
        />

        <MediaDirectory
            v-for="dir in content.directories"
            :key="dir.id"
            :directory="dir"
            @media-clicked="mediaClicked"
        />
    </depliantWindow>
</template>

<script setup>
import { ref } from 'vue'
import apiMedia from '@/javascript/api/api_media.js'
import MediaViewer from '@/subcomponents/unitary_elements/media_viewer.vue'
import MediaDirectory from '@/subcomponents/depliants/media_depliant_directory.vue' // récursif
import depliantWindow from '@/subcomponents/unitary_elements/depliantWindow.vue'   // 👈 manquant
import { Couleurs } from '@/javascript/constants/colors.js'

const props = defineProps({
    directory: Object
})

const loading = ref(false)
const loaded = ref(false)

const content = ref({
    files: [],
    directories: []
})

const emit = defineEmits(['media-clicked'])

function mediaClicked(media) {
    emit('media-clicked', media)
}

async function loadDirectory() {
    if (loaded.value) return

    loading.value = true

    const result = await apiMedia.get_children_directory(props.directory.id)
    const response = result.data
    if (response.success){
        Object.values(response.medias).forEach(media => {
            if (media.type === "dir") {
                content.value.directories.push(media)
            } else {
                content.value.files.push(media)
            }
        })
    }

    if (content.value.files.length > 0) {
        let urls_100 = {}
        let urls_preview = {}

        content.value.files.forEach(file => {
            urls_100[file.id] = file.thumbnail_100
            urls_preview[file.id] = file.thumbnail_300
        })

        const [res100, resPreview] = await Promise.all([
            apiMedia.get_thumbnails_medias(urls_100),
            apiMedia.get_thumbnails_medias(urls_preview)
        ])

        console.log("Thumbnails fetched:", resPreview.data.thumbnails)

        // Forcer la réactivité avec map() au lieu de muter directement
        content.value.files = content.value.files.map(file => ({
            ...file,
            thumbnail_100: res100.data.thumbnails[file.id] ?? file.thumbnail_100,
            thumbnail_300: resPreview.data.thumbnails[file.id] ?? file.thumbnail_300,
        }))
        console.log("Thumbnails updated:", content.value.files)
    }
    

    loaded.value = true
    loading.value = false
}
</script>