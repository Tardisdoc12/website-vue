<template>
    <div>{{ "Ceci sert de page test" }}</div>
    <PersonalNotes
        :Title="'Mes Notes'"
        :can-update="true"
    />    
</template>

<script>
import PersonalNotes from '@/subcomponents/depliants/personal_notes.vue';

export default{

    components:{
        PersonalNotes
    }
}
</script>