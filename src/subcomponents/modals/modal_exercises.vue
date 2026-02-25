<template>
    <ModalComponent
        :title="title"
        @changeBool="Cancel"
    >
        <!-- Choix des Exercices à ajouter ou supprimer -->
        <SearchComponent
            :list="list"
            :ColumnToShow="ColumnsToShow"
            @select="selectElement"
        />
    </ModalComponent>
</template>

<script>
import ModalComponent from '../unitary_elements/modalComponent.vue';
import SearchComponent from '../unitary_elements/search_component.vue';

export default{
    emits: [
        "cancelSignal",
        'add'
    ],


    props:{
        title:{
            type: String,
            required: true
        },

        list:{
            type:Array,
            required:true
        }
    },

    data(){
        return {
            ColumnsToShow:{
                'tag': 'Nom'
            },
        }
    },
    
    methods:{
        selectElement(element){
            this.$emit("add",element)
            this.$emit('cancelSignal')
        },
        Cancel(){
            this.$emit('cancelSignal')
        }
    },

    components:{
        ModalComponent,
        SearchComponent
    }
}
</script>