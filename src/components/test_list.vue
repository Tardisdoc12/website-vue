<template>
    <div>{{ "Ceci sert de page test" }}</div>
    <PersonalNotes
        :Title="'Mes Notes'"
        :can-update="true"
    />
    <font-awesome-icon
        icon="fa-solid fa-star"
        :style="{
            'color': isFav ? 'gold' : 'grey'
        }"
        @click="()=>{isFav=!isFav}"
    >
    </font-awesome-icon>
</template>

<script>
import PersonalNotes from '@/subcomponents/depliants/personal_notes.vue';

export default{

    data(){
        return {
            isFav: false
        }
    },

    components:{
        PersonalNotes
    }
}
</script>