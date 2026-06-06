<template>
    <DepliantWindow
        :title="'Evènements'"
        :backgroundColorOpen="Couleurs.dark_blue"
        :writenColorOpen="Couleurs.white"
        :border-color="Couleurs.dark_blue"
        :border-color-open="Couleurs.dark_blue"
        :borderWindowColor="Couleurs.dark_blue"
        :is-opoen-forced="true"
        v-bind="$attrs"
    >
        <event_card
            v-for="event in upcomingEvents"
            :event="event"
            :is-encadrant="false"
            @click-event-card="OnClickEventCard"
        />
    </DepliantWindow>

    <ModalEventUninscript
        v-if="OpenModalInscription"
        :event="eventSelected"
        :user-connected="user"
        @cancelSignal="()=>{OpenModalInscription = false}"
        @eventDeleted="deleteEvent"
    />
</template>

<script>
import event_card from '@/subcomponents/unitary_elements/event_card.vue';
import { Couleurs } from '@/javascript/constants/colors'
import DepliantWindow from '@/subcomponents/unitary_elements/depliantWindow.vue';
import ModalEventUninscript from '@/subcomponents/modals/modal_event_uninscript.vue'

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
            OpenModalInscription: false,
            eventSelected: null,
        }
    },

    computed: {
        upcomingEvents() {
            if (!this.user || !this.user.events) return [];

            const today = new Date();
            const todayDateOnly = new Date(
                today.getFullYear(),
                today.getMonth(),
                today.getDate()
            );

            return this.user.events.filter(event => {
                const eventDate = new Date(event.startDate);

                const eventDateOnly = new Date(
                    eventDate.getFullYear(),
                    eventDate.getMonth(),
                    eventDate.getDate()
                );

                // Visible le jour J, supprimé le lendemain
                return eventDateOnly.getTime() + 24 * 60 * 60 * 1000 > todayDateOnly.getTime();
            });
        }
    },

    methods: {
        OnClickEventCard(event) {
            this.OpenModalInscription = true
            this.eventSelected = event
            this.eventSelected.isInscript = true
        },

        deleteEvent(eventId) {
            const eventIndex = this.user.events.findIndex(event => event.id === eventId);
            if (eventIndex !== -1) {
                this.user.events.splice(eventIndex, 1);
            }
        },
    },

    components:{
        event_card,
        DepliantWindow,
        ModalEventUninscript,
    }
}
</script>

<style>
</style>