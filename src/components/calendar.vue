<template>
    <div class="calendar-wrapper">
        <FullCalendar
            ref="fullCalendar"
            :options="calendarOptions"
        />
    </div>
    
    <ModalEvents 
        v-if="seeModalEvent"
        :form="eventSelected"
        @cancelSignal="(e) => {seeModalEvent=e; eventSelected={}}"
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
import ModalEvents from "./subcomponents/modal_event.vue"
import ModalInscript from "./subcomponents/modal_form_inscription.vue"
import eventsService from '@/javascript/axios_events.js';
import Categories from "@/javascript/constants"
import { jwtDecode } from "jwt-decode"
import api from "../javascript/users_wp.js"
import { toRaw } from 'vue';

export default {
    
    data() {
        return {
            seeModalEvent: false,
            inscribe: false,
            eventSelected: {},
            events: [],
            user:{},
        }
    },

    async mounted() {
        this.events = await eventsService.getAllEvents();

        const token = sessionStorage.getItem("mps_moto")
        console.log(token)
        if (token) {
            const decoded = jwtDecode(token)
            const user_id = decoded.data.user.id
            console.log(user_id)
            const user_info = await api.get_user(user_id)
            console.log(user_info)
            this.user = user_info.user
        }
    },

    computed: {
        eventsList() {
                return this.events.map((e) => {
                    return {
                        start: e.startDate,
                        end: e.endDate,
                        event_id:e.id,
                        ...e
                    }
                }
                )
        },

        calendarOptions() {
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
                aspectRatio: 1.2,
                eventContent: this.renderEvent,
                buttonText: {
                    today: "Aujourd'hui",
                    month: "Mois",
                    week: "Semaine",
                    day: "Jour",
                    list: "Liste",
                },
                dayCellDidMount: this.dayRender,
            }
        },
    },
    methods: {
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
            if (parseInt(arg.event.extendedProps.subscribePlace) > 0) {
                number += parseInt(arg.event.extendedProps.subscribePlace)
            }
            number = number - Array.from(arg.event.extendedProps.users).length;
            
            const hour = arg.timeText
            const wrapper = document.createElement('div');
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
    components: { FullCalendar, ModalEvents, ModalInscript },
};
</script>

<style>
.fc .event-font {
    font-size: 10px;
}

.fc .background-card {
  background-color: rgba(50,255,255,0.2);
  border-radius: 4px;
  display: block;
  padding: 2px 4px;
  max-width: 100% !important;
  overflow: hidden;
}

.fc .event-row {
  align-items: stretch;
  width: 100%;
  min-width: 0;
}

.fc .event-card {
  background-color: aqua;
  border-top-right-radius: 12px;
  border-bottom-right-radius: 12px;
  width: 8px;
}

.fc .event-content {
  flex: 1;
  padding-top: 2px;
  padding-bottom: 2px;
  padding-left: 8px;
  line-height: 1.2;
  min-width: 0;
}

.fc .event-content b {
  white-space: nowrap;       /* Pas de retour à la ligne */
  overflow: hidden;          /* Cache le surplus */
  text-overflow: ellipsis;   /* Ajoute ... */
  display: block;
  max-width: 100%;
}

.fc .event-content small {
  white-space: nowrap;       /* Pas de retour à la ligne */
  overflow: hidden;          /* Cache le surplus */
  text-overflow: ellipsis;   /* Ajoute ... */
  display: block;
  max-width: 100%;
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

.calendar-wrapper .fc {
  max-width: 900px; /* largeur max du calendrier */
  width: 100%;      /* occupe toute la largeur disponible */
}
</style>
