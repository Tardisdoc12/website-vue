<template>
    <PersonalNotes
        :Title="'Mes Notes'"
        :can-update="isMe"
    />
    <PersonalNotes
        :Title="'Suivi et Conseils'"
        :can-update="!isMe"
    />
</template>

<script>
import PersonalNotes from '@/subcomponents/depliants/personal_notes.vue';

export default{
    emits:["cancelSignal"],

    props:{
        user:{
            type: Object,
            required: false,
            default: null
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
        PersonalNotes
    }
}
</script>