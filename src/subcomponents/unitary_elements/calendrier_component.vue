<template>
    <div class="calendar-wrapper">
        <FullCalendar
            :key="allowedCreateEvent"
            ref="fullCalendar"
            :options="calendarOptions"
        />
    </div>
</template>

<script>
import FullCalendar from "@fullcalendar/vue3";
import dayGridPlugin from "@fullcalendar/daygrid";
import interactionPlugin from "@fullcalendar/interaction"
import listPlugin from '@fullcalendar/list';
import EventsFunctions from '@/javascript/constants/events_functions'
import { computed } from 'vue'

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

    computed: {
        userEvents() {
            return this.userConnected?.events ?? []
        },
        isAdherent() {
            const roles = this.userConnected?.roles ?? []
            return !roles.includes("non_adherent")
        },
        isEncadrant() {
            const roles = this.userConnected?.roles ?? []
            return (
            !roles.includes("non_adherent") &&
            !roles.includes("adherent")
            )
        },
        calendarOptions() {
            const customButtons = this.getCustomButtons()
            const rightToolbar = this.allowedCreateEvent
                ? 'today myCustomButton toggleView prev,next'
                : 'today toggleView prev,next';

            return {
                plugins: [dayGridPlugin, interactionPlugin, listPlugin],
                initialView: 'dayGridMonth',
                events: this.eventsList,
                selectable:true,
                eventClick: this.handleSelect,
                locale: 'fr',
                showNonCurrentDates: false,
                firstDay: 1,
                contentHeight: 'auto',
                eventContent: this.renderEvent,
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
        getCustomButtons() {
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
                    text: "Liste d'évènements",
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

        renderEvent(arg) {
            const title = arg.event.title;
            const nonAdherentsCount = computed(() =>
                {
                    if (!arg.event.extendedProps.users.length) return 0;
                    return arg.event.extendedProps.users.filter(u => u.is_adherent === "1").length
                }
            )
            let number = parseInt(arg.event.extendedProps.nonsubscribePlace) - nonAdherentsCount.value;
            let places_available = "inscriptions ouvertes"
            if (number <= 0) {
                places_available = "complet"
            }
            if (this.userConnected?.roles) {
                if(!this.userConnected.roles.includes("non_adherent")) {
                    number = parseInt(arg.event.extendedProps.subscribePlace) - arg.event.extendedProps.users.length + nonAdherentsCount.value;
                    if(number === 0){
                        places_available = "complet"
                    }
                    else {
                        places_available = "inscriptions ouvertes"
                    }
                }
            }
            if (isOutdated(arg.event)) {
                places_available = "inscriptions fermées"
            }
            const alreadyInscript = this.userEvents?.some(obj => Number(obj.event_id) === Number(arg.event.extendedProps.event_id)) ?? false
            if(alreadyInscript) {
                places_available = "déjà inscrit"
            }
            
            const hour = arg.timeText
            const wrapper = document.createElement('div');

            wrapper.style.width = "100%";        // prend toute la largeur
            wrapper.style.boxSizing = "border-box"; // évite les débordements
            wrapper.style.overflow = "hidden";   // coupe si trop long
            wrapper.style.display = "block"; // étendre comme un block
            
            wrapper.innerHTML = `
                <div class="background-card">
                    <div class="event-row">
                        <div class="event-card"></div>
                        <div class="event-content">
                            <span class="event-font">
                                ${hour}
                            </span>
                            <div>
                                <b class="event-font">${title}</b>
                            </div>
                            <div>
                                <small class="event-font">
                                    ${places_available}
                                </small>
                            </div>
                            ${this.isEncadrant ? `<div><p class="event-font">${arg.event.extendedProps.users.length} inscrits</p></div>` : ''}
                        </div>
                    </div>
                </div>
            `;
           
            let bgColor;
            let backgroundColorCard;
            let colorWritting = "rgba(0, 0, 0, 1)";
            if(new Date() < arg.event.start) {
                const duoColor = EventsFunctions.colorBg(arg.event.extendedProps.categorie)
                bgColor = duoColor[0]
                backgroundColorCard = duoColor[1]
            }
            else {
                bgColor = 'rgba(211, 211, 211, 1)'
                backgroundColorCard = 'rgba(211, 211, 211, 0.2)'
                colorWritting = "rgba(12, 12, 12, 0.68)"
            }

            wrapper.querySelector('.background-card').style.backgroundColor = backgroundColorCard;
            wrapper.querySelector('.event-card').style.backgroundColor = bgColor;
            wrapper.querySelector('.event-content').style.color = colorWritting;

            return { domNodes: [wrapper] };
        },

        handleSelect(e){
            const isBureau = this.userConnected?.roles ? this.userConnected.roles.includes("bureau") : false

            if (isOutdated(e.event)) {
                if (!isBureau) {
                    return;
                } 
            }
            const nonAdherentsCount = computed(() =>{
                if (!e.event.extendedProps.users.length) return 0;
                return e.event.extendedProps.users.filter(u => u.is_adherent === "1").length
            })

            
            this.seeModalEvent = !this.seeModalEvent
            const alreadyInscript = this.userEvents.some(obj => Number(obj.event_id) === Number(e.event.extendedProps.event_id))
            const eventSelected = {
                ...e.event.extendedProps,
                isInscript: alreadyInscript,
                nonsubscribePlace: e.event.extendedProps.nonsubscribePlace - nonAdherentsCount.value,
                subscribePlace: e.event.extendedProps.subscribePlace - e.event.extendedProps.users.length + nonAdherentsCount.value,
                title:e.event.title
            }
            this.$emit("eventSelect", eventSelected)
        },
    
    },
    
    components: {
        FullCalendar,
    }

}

</script>


<style>
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