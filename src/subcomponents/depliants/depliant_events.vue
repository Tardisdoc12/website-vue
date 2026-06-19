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
            v-for="args in upcomingEvents"
            :event="args.event"
            :is-encadrant="args.isEncadrant"
            :hour="args.hour"
            :title="args.title"
            :place="args.place"
            :places-available="args.placesAvailable"
            :background-color="args.backgroundColor"
            :background-color-card="args.backgroundColorCard"
            :color-writing="args.colorWriting"
            :key="args.event.id"
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
import EventsFunctions from '@/javascript/constants/events_functions'

function isOutdated(event) {
    const now = new Date();
    // Ajoute 1 heure à l'heure actuelle
    const limit = new Date(now.getTime() + 60 * 60 * 1000);

    if (event.start < limit) {
        return true; // Bloque seulement si on est à moins d'une heure
    }
    return false
}

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

            let events = this.user.events.filter(event => {
                const eventDate = new Date(event.startDate);

                const eventDateOnly = new Date(
                    eventDate.getFullYear(),
                    eventDate.getMonth(),
                    eventDate.getDate()
                );

                // Visible le jour J, supprimé le lendemain
                return eventDateOnly.getTime() + 24 * 60 * 60 * 1000 > todayDateOnly.getTime();
            });

            events = events.map(event => {
                return this.returnCorrectedEvent(event);
            });
            return events;
        }
    },

    methods: {
        returnCorrectedEvent(event) {
            
            const users = Array.isArray(event?.users)
                    ? event.users
                    : [];
            const nonAdherentsCount = users.filter(u => u.is_adherent === "0" && u.status === "inscrit").length
            const adherentsCount = users.filter(u => u.is_adherent === "1" && u.status === "inscrit").length
            let number = parseInt(event.nonsubscribePlace) - nonAdherentsCount;
            let places_available = "inscriptions ouvertes"
            if (number <= 0) {
                places_available = "complet"
            }
            if (this.userConnected?.roles) {
                if(!this.userConnected.roles.includes("non_adherent")) {
                    number = parseInt(event.subscribePlace) - adherentsCount;
                    if(number === 0){
                        places_available = "complet"
                    }
                    else {
                        places_available = "inscriptions ouvertes"
                    }
                }
            }
            if (isOutdated(event)) {
                places_available = "inscriptions fermées"
            }
            const alreadyInscript = this.userEvents?.some(obj => Number(obj.event_id) === Number(event.event_id)) ?? false
            if(alreadyInscript) {
                places_available = "déjà inscrit"
            }
            if(Number(event.closed_inscription) === 1) {
                places_available = "inscriptions fermées"
            }
            else if(Number(event.closed_inscription) === 2) {
                places_available = "complet"
            }
            else if(Number(event.closed_inscription) === 3) {
                places_available = "évènement dépassé"
            }

            let bgColor;
            let backgroundColorCard;
            let colorWritting = "rgba(0, 0, 0, 1)";
            if(new Date() < new Date(event.startDate)) {
                let categorie = event.categorie
                if(event.categorie === "") {
                    categorie = "seance"
                }
                const duoColor = EventsFunctions.colorBg(categorie)
                bgColor = duoColor[0]
                backgroundColorCard = duoColor[1]
            }
            else {
                bgColor = 'rgba(211, 211, 211, 1)'
                backgroundColorCard = 'rgba(211, 211, 211, 0.2)'
                colorWritting = "rgba(12, 12, 12, 0.68)"
            }

            return {
                event: event,
                hour: event.startDate,
                title: event.title,
                place: event.place,
                placesAvailable: places_available,
                backgroundColor: bgColor,
                backgroundColorCard: backgroundColorCard,
                colorWriting: colorWritting,
                isEncadrant: false,
                users: event.users ?? [],
            }
        },

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