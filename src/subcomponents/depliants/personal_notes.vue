<template>
    <DepliantWindow
        :title="Title"
        :backgroundColorOpen="'#2d5c7f'"
        :writenColorOpen="'#FFFFFF'"
        :border-color="'#2d5c7f'"
        :border-color-open="'#2d5c7f'"
        :is-opoen-forced="true"
        :width="'95%'"
    >
        <div v-if="!isModify">
            <p
                style="white-space: pre-line; padding: 0px 5px;"
            >
                {{ Texte }}
            </p>
            <div v-if="canUpdate" class="center-element" style="margin-top: 5px; margin-bottom: 5px;">
                <button
                    class="appearance-none button-base"
                    @click="()=>{isModify=true}"
                >
                    {{ "Modifier" }}
                </button>
            </div>
        </div>
        <div v-if="isModify">
            <textarea
                v-model="notesCopy"
                placeholder="Vous pouvez prendre des notes"
                class="block mx-auto"
                rows="6"
            >
            </textarea>
        
            <div class="center-element" style="margin-top: 5px; margin-bottom: 5px;">
                <button
                    class="appearance-none button-base"
                    @click="SaveNotes"
                >
                    {{ "Sauvegarder" }}
                </button>
            </div>
        </div>
    </DepliantWindow>
</template>

<script>
import DepliantWindow from '@/subcomponents/unitary_elements/depliantWindow.vue';
import apiNotes from '@/javascript/api/axios_notes'

export default{
    emits:["saveNotes"],
    
    watch: {
        notes: {
            immediate: true,
            handler(newVal){
                this.notesCopy = newVal?.note_write ?? ''
            }
        }
    },

    props:{
        Title:{
            type: String,
            required: true
        },

        notes:{
            type:Object,
            required:true
        },

        canUpdate:{
            type: Boolean,
            required: false,
            default: true
        }
    },

    data(){
        return {
            notesCopy: this.notes?.note_write,
            isModify: this.canUpdate,
        }
    },

    computed:{
        Texte(){
            if(this.notesCopy === ''){
                return 'Veuillez modifier pour avoir un texte ici'
            }
            return this.notesCopy
        }
    },

    methods:{
        async SaveNotes(){
            if(!this.notes?.note_write){
                const result = await apiNotes.add_notes({
                    'is_personal': this.notes.is_personal,
                    'user_id': this.notes.user_id,
                    'notes': this.notesCopy
                })
                if(result?.data?.success){
                    this.$emit("saveNotes",this.notesCopy)
                }
            }
            else{
                const result = await apiNotes.update_notes(this.notes.wp_user_id, Number(this.notes.is_personal), this.notesCopy)
                if(result?.data?.success){
                    this.$emit("saveNotes", this.notesCopy)
                }
            }
            this.isModify = false
        }
    },

    components:{
        DepliantWindow
    }
}

</script>
