<template>
    <ModalComponent
        :title="title"
        :width="'80%'"
        @changeBool="Cancel"
    >
        <SearchComponent
            :list="listToPass"
            :ColumnToShow="ColumnList"
            @select="selectElement"
        />
    </ModalComponent>
</template>

<script>
import ModalComponent from '@/subcomponents/unitary_elements/modalComponent.vue';
import SearchComponent from '@/subcomponents/unitary_elements/search_component.vue';

export default{
    emits:['select', 'cancelSignal'],

    props:{
        listToPass:{
            type: Array,
            required: true,
        },

        ColumnList:{
            type: Object,
            required: false,
            default: null
        },

        title:{
            type: String,
            required: true
        }
    },

    methods:{
        selectElement(element){
            this.$emit('select', element)
        },

        Cancel() {
            this.$emit('cancelSignal')
        }
    },

    components:{
        ModalComponent,
        SearchComponent
    }

}

</script>