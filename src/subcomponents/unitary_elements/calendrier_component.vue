<template>
    <div class="calendar-wrapper">
        <FullCalendar
            :key="allowedCreateEvent"
            ref="fullCalendar"
            :options="calendarOptions"
        >
            <template #eventContent="arg">
                <EventCard
                    :event="arg.event.extendedProps"
                    :hour="arg.timeText"
                    :title="arg.event.title"
                    :place="arg.event.extendedProps.place"
                    :placesAvailable="getPlacesAvailableMessage(arg.event)"
                    :backgroundColor="getEventColors(arg.event).bg"
                    :backgroundColorCard="getEventColors(arg.event).card"
                    :colorWriting="getEventColors(arg.event).text"
                    :isEncadrant="isEncadrantComp"
                    :users="arg.event.extendedProps.users ?? []"
                    :isInscrit="isAlreadyInscrit(arg.event)"
                    :userConnected="userConnected"
                    @click-event-card="onEventCardClick(arg.event, $event)"
                />
            </template>
        </FullCalendar>
    </div>
</template>

<script>
import FullCalendar from "@fullcalendar/vue3";
import dayGridPlugin from "@fullcalendar/daygrid";
import interactionPlugin from "@fullcalendar/interaction"
import listPlugin from '@fullcalendar/list';
import EventsFunctions from '@/javascript/constants/events_functions'
import EventCard from "@/subcomponents/unitary_elements/event_card.vue"
import { isEncadrant, isBureau } from "@/javascript/constants/roles";

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
        eventsList: {
            type: Object,
            required: true
        },
        userConnected: {
            type: Object,
            required: false,
            default: () => ({}),
        },
        allowedCreateEvent: {
            type: Boolean,
            required: false,
            default: false,
        }
    },

    data(){
        return {
            isMobile: false,
            mediaQuery: null,
        }
    },

    mounted(){
        this.mediaQuery = window.matchMedia("(max-width: 768px)")
        this.isMobile = this.mediaQuery.matches
        this.mediaQuery.addEventListener("change", this.onChange)
        this.$nextTick(() => {
            const calendarApi = this.$refs.fullCalendar.getApi()
            calendarApi.changeView(this.isMobile ? 'listMonth' : 'dayGridMonth')
        })
    },

    beforeUnmount() {
        this.mediaQuery.removeEventListener("change", this.onChange)
    },

    computed: {
        userEvents() {
            return this.userConnected?.events ?? []
        },
        isAdherent() {
            const roles = this.userConnected?.roles ?? []
            return !roles.includes("non_adherent")
        },
        isEncadrantComp() {
            const roles = this.userConnected?.roles ?? []
            return isEncadrant(roles)
        },
        calendarOptions() {
            const customButtons = this.getCustomButtons()
            const rightToolbar = this.allowedCreateEvent
                ? 'today myCustomButton toggleView prev,next'
                : 'today toggleView prev,next';

            const firstView = this.isMobile ? 'listMonth' : 'dayGridMonth'
            return {
                plugins: [dayGridPlugin, interactionPlugin, listPlugin],
                initialView: firstView,
                events: this.eventsList,
                selectable:true,
                eventClick: this.handleSelect,
                locale: 'fr',
                showNonCurrentDates: false,
                firstDay: 1,
                contentHeight: 'auto',
                buttonText: {
                    today: "Aujourd'hui",
                    month: "Mois",
                    week: "Semaine",
                    day: "Jour",
                    list: "Liste",
                },
                dayCellDidMount: this.dayRender,
                customButtons,
                // Configurer la toolbar pour inclure le bouton
                headerToolbar: {
                    right: rightToolbar, // le bouton apparaît à côté de "today"
                    left: 'title'
                }
            }
        },
    },
    methods: {
        onChange(e){
            this.isMobile = e.matches
        },
        getCustomButtons() {
            const firstView = this.isMobile ? 'listMonth' : 'dayGridMonth'

            return {
                ...(
                    this.allowedCreateEvent
                        ? {
                            myCustomButton: {
                                text: 'Créer un évènement',
                                click: () => {
                                    this.$emit("createEvent")
                                }
                            }
                        }
                        : {}
                ),
                toggleView: {
                    text: firstView === 'listMonth'
                        ? "Calendrier"
                        : "Liste d'évènements",
                    click: this.toggleViewClick
                }
            }
        },
        toggleViewClick() {
            const calendarApi = this.$refs.fullCalendar.getApi();
            const currentView = calendarApi.view.type;
            const buttons = calendarApi.getOption('customButtons');
            if (currentView === 'dayGridMonth') {
                calendarApi.changeView('listMonth');
                // mettre à jour le texte du bouton
                calendarApi.setOption('customButtons', {
                    ...buttons,
                    toggleView: {
                        ...buttons.toggleView,
                        text: "Calendrier"
                    }
                });
            } else {
                calendarApi.changeView('dayGridMonth');
                calendarApi.setOption('customButtons', {
                    ...buttons,
                    toggleView: {
                        ...buttons.toggleView,
                        text: "Liste d'évènements"
                    }
                });
            }
        },

        dayRender(arg) {
            arg.isDisabled = arg.isPast
            if (arg.isPast) {
                let inner = arg.el.querySelector('.fc-daygrid-day-number')
                if (inner) {
                    inner.style.color = '#808080'
                }
            }
        },

        getPlacesAvailableMessage(event) {
            const users = Array.isArray(event.extendedProps?.users)
                ? event.extendedProps.users
                : [];

            const nonAdherentsCount = users.filter(u => u.is_adherent === "0" && u.status === "inscrit").length
            const adherentsCount = users.filter(u => u.is_adherent === "1" && u.status === "inscrit").length

            const isNonAdherent = this.userConnected?.roles?.includes("non_adherent") ?? true;
            const placesAvailableNonAdherent = parseInt(event.extendedProps.nonsubscribePlace) - nonAdherentsCount
            const placesAvailableAdherent = parseInt(event.extendedProps.subscribePlace) - adherentsCount
            let placesAvailableMessage = 'inscriptions ouvertes'
            const alreadyInscript = this.isAlreadyInscrit(event)

            if(isNonAdherent && placesAvailableNonAdherent <= 0) {
                placesAvailableMessage = 'complet'
            }

            if(!isNonAdherent && placesAvailableAdherent <= 0 && parseInt(event.extendedProps.subscribePlace) > 0) {
                placesAvailableMessage = 'complet'
            }

            if (isOutdated(event)) {
                placesAvailableMessage = "inscriptions fermées"
            }

            if(Number(event.extendedProps.closed_inscription) === 1) {
                placesAvailableMessage = "inscriptions fermées"
            }
            else if(Number(event.extendedProps.closed_inscription) === 2) {
                if (isBureau(this.userConnected?.roles ?? [])) {
                    placesAvailableMessage = "rendu complet"
                }
                else{
                    placesAvailableMessage = "complet"
                }
            }
            else if(Number(event.extendedProps.closed_inscription) === 3) {
                placesAvailableMessage = "évènement dépassé"
            }
            if(alreadyInscript) {
                placesAvailableMessage = "vous êtes inscrit"
            }

            return placesAvailableMessage
        },

        getEventColors(event) {
            let bgColor;
            let backgroundColorCard;
            let colorWritting = "rgba(0, 0, 0, 1)";

            if(new Date() < event.start) {
                let categorie = event.extendedProps.categorie
                if(event.extendedProps.categorie === "") {
                    categorie = "Seance"
                }
                const duoColor = EventsFunctions.get_color_events_by_categorie(categorie)
                bgColor = duoColor[0]
                backgroundColorCard = duoColor[1]
            }
            else {
                bgColor = 'rgba(211, 211, 211, 1)'
                backgroundColorCard = 'rgba(211, 211, 211, 0.2)'
                colorWritting = "rgba(12, 12, 12, 0.68)"
            }

            return { bg: bgColor, card: backgroundColorCard, text: colorWritting }
        },

        isAlreadyInscrit(event) {
            return this.userEvents?.some(obj => Number(obj.event_id) === Number(event.extendedProps.event_id)) ?? false
        },

        onEventCardClick(event, extendedProps) {
            this.handleSelect({
                event: {
                    extendedProps: extendedProps,
                    title: event.title,
                    start: event.start,
                }
            })
        },

        handleSelect(e){
            const isBureau = this.userConnected?.roles ? this.userConnected.roles.includes("bureau") : false

            if (isOutdated(e.event)) {
                if (!isBureau) {
                    return;
                } 
            }

            const users = e.event.extendedProps?.users ?? []
            const nonAdherentsCount = users.filter(u => u.is_adherent === "0" && u.status === "inscrit").length
            const adherentsCount = users.filter(u => u.is_adherent === "1" && u.status === "inscrit").length
            const nbr_attentes = users.filter(u => u.status === "attente").length

            this.seeModalEvent = !this.seeModalEvent
            const alreadyInscript = this.userEvents.some(obj => Number(obj.event_id) === Number(e.event.extendedProps.event_id))
            const eventSelected = {
                ...e.event.extendedProps,
                isInscript: alreadyInscript,
                nonsubscribePlace: e.event.extendedProps.nonsubscribePlace,
                subscribePlace: e.event.extendedProps.subscribePlace,
                title:e.event.title,
                nbr_adherents: adherentsCount,
                nbr_non_adherents: nonAdherentsCount,
                nbr_attente: nbr_attentes,
            }
            this.$emit("eventSelect", eventSelected)
        },

    },

    components: {
        FullCalendar,
        EventCard,
    }

}

</script>


<style>
.calendar-wrapper {
  --fc-button-bg-color: var(--main-color);
  --fc-button-border-color: var(--main-color);
  --fc-button-hover-bg-color: var(--secondary-color);
  --fc-button-hover-border-color: var(--secondary-color);
  --fc-button-active-bg-color: var(--secondary-color);
  --fc-button-active-border-color: var(--secondary-color);
  --fc-button-text-color: var(--writing-main-color);
}

.fc-daygrid-event {
  text-decoration: none !important;
  color: inherit !important;
}

.fc-daygrid-event a {
  text-decoration: none !important;
  color: inherit !important;
}

.event-font {
    font-size: 10px;
}

.background-card {
  background-color: rgba(50,255,255,0.2);
  border-radius: 4px;
  padding: 4px 2px;
}

.event-row {
    display:flex;
    align-items: stretch;
    
}

.event-card {
    background-color: aqua;
    border-top-right-radius: 12px;
    border-bottom-right-radius: 12px;
    width: 8px;
    min-width: 8px;   /* 👈 empêche la compression */
    flex-shrink: 0;
}

.event-content {
  flex: 1;
  padding-top: 2px;
  padding-bottom: 2px;
  padding-left: 8px;
  line-height: 1.2;
}

.event-content b {
  white-space: nowrap;       /* Pas de retour à la ligne */
  overflow: hidden;          /* Cache le surplus */
  text-overflow: ellipsis;
}

.event-content small {
  white-space: nowrap;       /* Pas de retour à la ligne */
  overflow: hidden;          /* Cache le surplus */
  text-overflow: ellipsis;
}

.fc-day-disabled {
    color: rgba(241, 241, 241, 0.2)
}

.fc-toolbar-title {
  text-transform: capitalize; /* met juste la 1ère lettre en majuscule */
}

.calendar-wrapper {
  display: flex;
  justify-content: center; /* centre horizontalement */
  padding: 20px;
}


</style>