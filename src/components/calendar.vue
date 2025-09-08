<template>
    <div class="calendar-wrapper">
        <FullCalendar
            ref="fullCalendar"
            :options="calendarOptions"
        />
    </div>
    
    <ModalCreateEvent
        v-if="cancelCreateEvent"
        @cancelSignal="cancelCreateEvent=false"
        :onSuccess="creationSuccess"
    />

    <ModalEvents 
        v-if="seeModalEvent"
        :showDeleteButton="allowedCreateEvent"
        :form="eventSelected"
        :roles="user?.roles"
        @cancelSignal="closeEvent"
        @inscriptWanted="(e) => {inscribe=e; seeModalEvent=!seeModalEvent}"
    />

    <ModalInscript
        v-if="inscribe"
        :isSeance="eventSelected.categorie === 'seance'"
        :event_id="eventSelected.event_id"
        :user="user"
        @cancelSignal="(e) => {inscribe=e; eventSelected={}}"
        @inscritValid="(e) => {inscribe=e; eventSelected={}}"
    />
</template>

<script>
import FullCalendar from "@fullcalendar/vue3";
import dayGridPlugin from "@fullcalendar/daygrid";
import interactionPlugin from "@fullcalendar/interaction"
import ModalCreateEvent from "@/components/formulaire.vue"
import ModalEvents from "./subcomponents/modal_event.vue"
import ModalInscript from "./subcomponents/modal_form_inscription.vue"
import eventsService from '@/javascript/axios_events.js';
import Categories from "@/javascript/constants"
import { jwtDecode } from "jwt-decode"
import api from "../javascript/users_wp.js"

export default {
    
    data() {
        return {
            seeModalEvent: false,
            inscribe: false,
            cancelCreateEvent:false,
            eventSelected: {},
            events: [],
            user:{},
            allowedCreateEvent:false,
        }
    },

    async mounted() {
        this.events = await eventsService.getAllEvents();

        const token = sessionStorage.getItem("mps_moto")
        if (token) {
            const decoded = jwtDecode(token)
            const user_id = decoded.data.user.id
            const user_info = await api.get_user(user_id)
            this.user = user_info.user
            listB = this.user.roles
            listA = ['bureau', 'administrator']
            this.allowedCreateEvent = listB.some(el => listA.includes(el));
        }
    },

    computed: {
        eventsList() {
                return this.events.map((e) => {
                    let endDate = e.endDate || e.startDate;
                    return {
                        start: e.startDate,
                        end: endDate,
                        event_id:e.id,
                        backgroundColor: 'transparent',
                        borderColor: 'transparent',
                        ...e
                    }
                }
                )
        },

        calendarOptions() {
            // Créer customButtons seulement si nécessaire
            const customButtons = this.allowedCreateEvent ? {
                myCustomButton: {
                    text: 'Créer un évènement',
                    click: () => {
                        this.cancelCreateEvent = true;
                    }
                }
            } : {};
            const rightToolbar = this.allowedCreateEvent ? 'today myCustomButton prev,next' : 'today prev,next';

            return {
                plugins: [dayGridPlugin, interactionPlugin],
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
        async closeEvent(e) {
            this.seeModalEvent=e;
            this.eventSelected={};
            this.events = await eventsService.getAllEvents();
        },

        async creationSuccess() {
            this.cancelCreateEvent=false
            this.events = await eventsService.getAllEvents();
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
            let number = parseInt(arg.event.extendedProps.nonsubscribePlace);
            if (this.user?.roles) {
                if(!this.user.roles.includes("non_adherent")) {
                    number = parseInt(arg.event.extendedProps.subscribePlace);
                }
            }
            
            const hour = arg.timeText
            const wrapper = document.createElement('div');

            wrapper.style.width = "100%";        // prend toute la largeur
            wrapper.style.boxSizing = "border-box"; // évite les débordements
            wrapper.style.overflow = "hidden";   // coupe si trop long
            wrapper.style.display = "block"; // étendre comme un block
            if (number >= 0) {
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
                                    ${number} places disponibles
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                `;
            }
            else {
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
                                    inscriptions ouvertes
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                `;
            }
            let bgColor;
            let backgroundColorCard;
            let colorWritting = "rgba(0, 0, 0, 1)";
            if(new Date() < arg.event.start) {
                const duoColor = Categories.colorBg(arg.event.extendedProps.categorie)
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
            const today = new Date();
            if (e.event.start < today)
            {
                return;
            }
            this.seeModalEvent = !this.seeModalEvent
            this.eventSelected = {
                ...e.event.extendedProps,
                title:e.event.title
            }
        }
    },
    components: { FullCalendar, ModalEvents, ModalInscript, ModalCreateEvent },
};
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
