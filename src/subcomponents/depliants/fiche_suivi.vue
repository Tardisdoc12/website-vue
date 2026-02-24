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
        :files="files"
    />
</template>

<script>
import PersonalNotes from '@/subcomponents/depliants/personal_notes.vue';
import DepliantAllExercices from './DepliantAllExercices.vue';
import apiFavoris from '@/javascript/api/axios_favoris'

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

    async mounted(){
        const response = await apiFavoris.get_favoris_by_user(this.user.ID)
        if(response?.data?.success){
            this.files = response.data.favoris
        }
    },

    data() {
        return {
            files: []
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