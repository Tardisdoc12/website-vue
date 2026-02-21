<template>
    <DepliantWindow
        :title="'Evènements'"
        :backgroundColorOpen="Couleurs.dark_blue"
        :writenColorOpen="Couleurs.white"
        :border-color="Couleurs.dark_blue"
        :border-color-open="Couleurs.dark_blue"
        :borderWindowColor="Couleurs.dark_blue"
        :is-opoen-forced="true"
    >
        <event_card
            v-for="event in user.events"
            :event="event"
            :is-encadrant="false"
            @click="Click"
        />
    </DepliantWindow>
</template>

<script>
import event_card from '@/subcomponents/unitary_elements/event_card.vue';
import { Couleurs } from '@/javascript/constants/colors'
import DepliantWindow from '@/subcomponents/unitary_elements/depliantWindow.vue';

export default{
    props:{
        user:{
            type: Object,
            required: true
        }
    },

    data() {
        return {
            Couleurs,
        }
    },

    methods:{
        Click(){
            alert("coucou")
        }
    },

    components:{
        event_card,
        DepliantWindow
    }
}
</script>

<style>
</style>