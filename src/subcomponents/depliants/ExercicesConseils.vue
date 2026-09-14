<template>
    <DepliantWindow
        :title="'Exercices Conseillés'"
        :width="'100%'"
        :backgroundColor="Couleurs.cyan"
        :borderColorOpen="'var(--secondary-color)'"
        :borderColor="'var(--secondary-color)'"
        :backgroundColorOpen="Couleurs.cyan"
        :writenColor="Couleurs.main_color"
        :writenColorOpen="Couleurs.main_color"
        :showBorder="false"
        :borderRadius="'0px'"
    >
        <FileComponent
            v-for="file in files"
            :file="file"
            :canBeFavoris="false"
            :canBeUpdated="false"
        />
    </DepliantWindow>
</template>

<script>
import DepliantWindow from '../unitary_elements/depliantWindow.vue';
import FileComponent from '../unitary_elements/file_component.vue';
import { Couleurs } from '@/javascript/constants/colors';

export default{
    props:{
        files:{
            type : Array,
            required: true,
        }
    },

    data(){
        return {
            Couleurs,
        }
    },

    components:{
        DepliantWindow,
        FileComponent
    }
}

</script>