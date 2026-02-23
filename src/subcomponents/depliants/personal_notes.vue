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
                v-model="notes"
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

export default{
    emits:["saveNotes"],

    props:{
        Title:{
            type: String,
            required: true
        },
        canUpdate:{
            type: Boolean,
            required: false,
            default: true
        }
    },

    data(){
        return {
            notes:'',
            isModify: this.canUpdate,
        }
    },

    computed:{
        Texte(){
            if(this.notes === ''){
                return 'Veuillez modifier pour avoir un texte ici'
            }
            return this.notes
        }
    },

    methods:{
        SaveNotes(){
            this.$emit("saveNotes", this.notes)
            this.isModify = false
        }
    },

    components:{
        DepliantWindow
    }
}

</script>
