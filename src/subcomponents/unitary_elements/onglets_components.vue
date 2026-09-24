<template>
    <div class="steps-tabs">
        <button
            v-for="(tab, index) in OngletList"
            :key="index"
            type="button"
            class="step-tab"
            :class="{ active: steps === index }"
            @click="goToStep(index)"
        >
            <span class="step-tab-index">{{ index + 1 }}</span>
            <span class="step-tab-text" >{{ tab }}</span>
        </button>
    </div>
</template>

<script>

export default {
    name: 'OngletsComponents',

    signals: [
        'update:steps'
    ],

    props: {
        OngletList: {
            type: Array,
            required: true
        },
        steps: {
            type: Number,
            required: true
        }
    },
    methods: {
        goToStep(index) {
            this.$emit('update:steps', index);
        }
    }
};
</script>

<style scoped>
.steps-tabs {
    display: flex;
    gap: 4px;
    margin-bottom: 20px;
    border-bottom: 2px solid #e0e0e0;
}

.step-tab {
    appearance: none;
    background: none;
    border: none;
    padding: 10px 16px;
    font-size: 0.9rem;
    color: #888;
    cursor: pointer;
    border-bottom: 2px solid transparent;
    transition: color 0.15s ease, border-color 0.15s ease;
    display: flex;
    align-items: center;
    gap: 6px;

    min-width: 0;   /* Permet au bouton de descendre en dessous de la taille de son texte */
    flex: 1 1 0%;
}

.step-tab:hover {
    color: var(--main-color, #333);
}

.step-tab.active {
    color: var(--main-color, #333);
    font-weight: 600;
    border-bottom-color: var(--main-color, #333);
}

.step-tab-index {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #e0e0e0;
    color: #666;
    font-size: 0.75rem;
    font-weight: 600;
    flex-shrink: 0;
}


/* Le texte de l'onglet */
.step-tab-text {
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
    min-width: 0; /* Force le calcul du texte pour l'ellipse */
}

.step-tab.active .step-tab-index {
    background: var(--main-color, #333);
    color: white;
}
</style>