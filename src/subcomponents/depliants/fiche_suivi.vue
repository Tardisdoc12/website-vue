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
        :conseils="conseils"
    />
</template>

<script>
import PersonalNotes from '@/subcomponents/depliants/personal_notes.vue';
import DepliantAllExercices from './DepliantAllExercices.vue';
import apiFavoris from '@/javascript/api/axios_favoris'
import apiConseils from '@/javascript/api/axios_conseils'

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
            this.files = response.data.favoris ?? []
        }
        const response2 = await apiConseils.get_conseils_by_user(this.user.ID)
        if(response2?.data?.success){
            this.conseils = response.data.conseils ?? []
        }
    },

    data() {
        return {
            files: [],
            conseils: []
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