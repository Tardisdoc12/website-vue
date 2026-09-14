<template>
    <DepliantWindow
        :title="'Mes Exercices'"
        :width="'100%'"
        :backgroundColor="'var(--depliant-background-color)'"
        :borderColorOpen="'var(--secondary-color)'"
        :borderColor="'var(--secondary-color)'"
        :backgroundColorOpen="'var(--depliant-background-color)'"
        :writenColor="'var(--main-color)'"
        :writenColorOpen="'var(--main-color)'"
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

export default{
    props:{
        files:{
            type : Array,
            required: true,
        }
    },

    components:{
        DepliantWindow,
        FileComponent
    }
}

</script>