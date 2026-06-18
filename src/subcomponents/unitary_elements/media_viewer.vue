<template>
    <div class="gallery-wrapper">

        <!-- Chargement -->
        <div v-if="loading" class="state-overlay">
            <span class="spinner" />
            Chargement…
        </div>

        <!-- Erreur -->
        <div v-else-if="error" class="state-overlay error">
            ⚠ {{ error }}
        </div>

        <!-- Grille -->
        <div v-else class="gallery-grid">
            <div
                v-for="media in medias"
                :key="media.id"
                class="thumb"
            >
                <img
                    v-if="media.thumbnails_100"
                    :src="media.thumbnails_100"
                    :alt="media.name ?? media.id"
                    @click="handleClick(media)"
                />
                <div v-else class="thumb-fallback" @click="handleClick(media)">
                    ✕
                </div>
            </div>
        </div>

    </div>
</template>

<script>
export default {
    name: "MediaViewer",

    emits: ['media-clicked'],

    props: {
        medias: {
            type: Array,
            required: true,
        },
        loading: {
            type: Boolean,
            default: false,
        },
        error: {
            type: String,
            default: null,
        },
    },

    methods: {
        handleClick(media) {
            this.$emit('media-clicked', media);
        },
    },
};
</script>

<style scoped>

.gallery-wrapper {
    width: 100%;
    height: 400px;
    overflow-y: auto;
    box-sizing: border-box;
    position: relative;
}

/* Grille : pas de hauteur fixe, grandit naturellement */
.gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
    gap: 6px;
    padding: 8px;
    box-sizing: border-box;
    width: 100%;
}

/* ── Vignette ── */
.thumb {
    aspect-ratio: 1;
    overflow: hidden;
    border-radius: 6px;
    background: #f0f0f0;
    cursor: pointer;
    min-height: 0;
    min-width: 0;
    width: 100%;
}

.thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.2s ease;
}

.thumb img:hover {
    transform: scale(1.07);
}

/* ── Fallback miniature absente ── */
.thumb-fallback {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #aaa;
    font-size: 1.2rem;
}

/* ── Overlay loading / erreur ── */
.state-overlay {
    grid-column: 1 / -1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 10px;
    height: 360px;
    color: #888;
    font-family: sans-serif;
    font-size: 14px;
}

.state-overlay.error {
    color: #c0392b;
}

/* ── Spinner ── */
.spinner {
    width: 28px;
    height: 28px;
    border: 3px solid #ddd;
    border-top-color: #555;
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>