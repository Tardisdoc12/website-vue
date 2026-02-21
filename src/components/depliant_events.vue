<template>
    <event_card
        :event="event"
        :is-encadrant="true"
        @click="Click"
    />
</template>

<script>
import event_card from './event_card.vue';
import { Events } from '@/javascript/constants/events_type'

export default{
    data() {
        return {
            event: {
                title:"Test",
                startDate: "8h",
                users:["toto","tutu"],
                categorie: Events.balade,
            }
        }
    },

    methods:{
        Click(){
            alert("coucou")
        }
    },

    components:{
        event_card,
    }
}
</script>

<style>
</style>