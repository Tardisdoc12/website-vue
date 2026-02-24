<template>
    <div>{{ "Ceci sert de page test" }}</div>
    <DepliantExercies
        :files="[]"
    />
</template>

<script>
import DepliantExercies from '@/subcomponents/depliants/DepliantAllExercices.vue';

export default{

    components:{
        DepliantExercies
    }
}
</script>