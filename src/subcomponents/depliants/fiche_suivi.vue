<template>
    <PersonalNotes
        :Title="'Mes Notes'"
        :notes="PersonnalNotes"
        :can-update="isMe"
    />
    <PersonalNotes
        :Title="'Suivi et Conseils'"
        :notes="AdvicesNotes"
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

        notes:{
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
        
        PersonnalNotes(){
            if(this.notes.length === 0){
                return {
                    is_personal: 0,
                    user_id: this.user.ID
                }
            }

            const note = this.notes.find(
                el => Number(el.is_personal) === 1
            )
            return note ?? {
                is_personal: 0,
                user_id: this.user.ID
            }
        },

        AdvicesNotes(){
            if(this.notes.length === 0){
                return {
                    is_personal: 0,
                    user_id: this.user.ID
                }
            }

            const note = this.notes.find(
                el => Number(el.is_personal) === 0
            )
            return note ?? {
                is_personal: 0,
                user_id: this.user.ID
            }
        },

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