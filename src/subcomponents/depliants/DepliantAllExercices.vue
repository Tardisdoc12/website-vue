<template>
    <DepliantWindow
        :title="'Exercices'"
        :backgroundColorOpen="Couleurs.dark_blue"
        :writenColorOpen="Couleurs.white"
        :border-color="Couleurs.dark_blue"
        :border-color-open="Couleurs.dark_blue"
        :borderWindowColor="Couleurs.dark_blue"
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