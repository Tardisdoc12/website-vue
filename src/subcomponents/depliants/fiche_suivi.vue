<template>
    <PersonalNotes
        :Title="'Mes Notes'"
        :can-update="isMe"
    />
    <PersonalNotes
        :Title="'Suivi et Conseils'"
        :can-update="!isMe"
    />
    <DepliantAllExercices
        :files="listExercices"
        :conseils="listConseils"
    />
</template>

<script>
import PersonalNotes from '@/subcomponents/depliants/personal_notes.vue';
import DepliantAllExercices from './DepliantAllExercices.vue';

export default{
    emits:["cancelSignal"],

    props:{
        user:{
            type: Object,
            required: false,
            default: null
        },

        listExercices:{
            type: Array,
            required: true
        },

        listConseils:{
            type: Array,
            required: true
        },

        isMe:{
            type:Boolean,
            required:false,
            default:true
        }
    },

    data() {
        return {
        }
    },

    computed:{
        Title() {
            if(this.user){
                return "Fiche de Suivi"
            }
            return "Error"
        }
    },

    methods:{

        Cancel() {
            this.$emit("cancelSignal")
        }
    },

    components:{
        PersonalNotes,
        DepliantAllExercices
    }
}
</script>