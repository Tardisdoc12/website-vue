<template>
    <DepliantWindow
        :title="'Exercices'"
        :backgroundColorOpen="'var(--secondary-color)'"
        :writenColorOpen="Couleurs.white"
        :border-color="'var(--secondary-color)'"
        :border-color-open="'var(--secondary-color)'"
        :borderWindowColor="'var(--secondary-color)'"
        :width="'95%'"
    >
        <DepliantExercies
            :files="files"
        />
        <ExercicesConseils
            :files="conseils"
        />
    </DepliantWindow>
</template>

<script>
import DepliantWindow from '../unitary_elements/depliantWindow.vue';
import DepliantExercies from './depliantExercies.vue';
import ExercicesConseils from './ExercicesConseils.vue';
import { Couleurs } from '@/javascript/constants/colors';

export default {
    props:{
        files:{
            type: Array,
            required: true
        },
        
        conseils:{
            type:Array,
            required: true
        }
    },

    data() {
        return {
            Couleurs
        }
    },

    components:{
        DepliantWindow,
        DepliantExercies,
        ExercicesConseils
    }
}
</script>