<template>
    <DepliantWindow
        :title="'Mes Exercices'"
        :width="'100%'"
        :backgroundColor="Couleurs.cyan"
        :borderColorOpen="Couleurs.dark_blue"
        :borderColor="Couleurs.dark_blue"
        :backgroundColorOpen="Couleurs.cyan"
        :writenColor="Couleurs.main_blue"
        :writenColorOpen="Couleurs.main_blue"
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