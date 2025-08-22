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
        :user="user.wordpress"
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
        this.events = [...this.events]
        const token = sessionStorage.getItem("mps_moto")
        if (token) {
            const decoded = jwtDecode(token)
            const user_id = decoded.data.user.id
            const user_info = await api.get_user(user_id)
            this.user = user_info
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
            
            let number = arg.event.extendedProps.nonsubscribePlace;
            if (arg.event.extendedProps.subscribePlace > 0) {
                number += arg.event.extendedProps.subscribePlace
            }
            number = number - arg.event.extendedProps.users.length;
            
            const hour = arg.timeText
            const wrapper = document.createElement('div');
            wrapper.innerHTML = `
                <span>${hour} <b>${title}</b></span><div><small>${number} slots available</small></div>`;

            const bgColor = Categories.colorBg(arg.event.extendedProps.categorie)

            wrapper.style.backgroundColor = bgColor;
            wrapper.style.padding = "2px 4px";
            wrapper.style.borderRadius = "4px";

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
