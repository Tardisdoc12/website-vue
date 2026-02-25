<template>
    <ModalComponent
        :title="title"
        @changeBool="Cancel"
    >
        <div
            style="margin-left: 20px; margin-right: 20px;margin-top: 10px; margin-bottom: 10px;"
        >
            <div class="flex flex-col gap-1">
                <select v-model="valueSelected">
                    <option disabled value="">Choisissez</option>
                    <option :key="index" v-for="(value, index) in listToSelectFrom" :value="value">{{ value?.[keyToDraw] }}</option>
                </select>
            </div>
            <div class="flex items-center justify-center " style="margin-bottom:10px;margin-top: 15px;">
                <button
                    :disabled="isDisable"
                    @click="Validate"
                    class="appearance-none button-base"
                    :style="{
                        '--btn-bg' : isDisable ? Couleurs.cyan : Couleurs.main_blue,
                        '--btn-hover-bg' : isDisable ? Couleurs.cyan : Couleurs.dark_blue
                    }"
                >
                    Valider le choix
                </button>
            </div>
        </div>
    </ModalComponent>
</template>

<script>
import ModalComponent from '../unitary_elements/modalComponent.vue';
import { Couleurs } from '@/javascript/constants/colors';

export default {
    emits:[
        "cancelSignal",
        "validate"
    ],

    props:{
        title:{
            type:String,
            required: true
        },

        listToSelectFrom:{
            type: Array,
            required: true
        },

        keyToDraw:{
            type:String,
            required: true
        }
    },

    data(){
        return {
            Couleurs,
            valueSelected: null,
        }
    },

    computed:{
        isDisable(){
            return this.valueSelected ? false : true
        }
    },

    methods:{
        Cancel(){
            this.$emits("cancelSignal")
        },

        Validate(){
            this.$emit("validate", this.valueSelected)
            this.$emit('cancelSignal')
        }
    },

    components:{
        ModalComponent
    }
}
</script>