<template>
    <div class="calendar-wrapper">
        <FullCalendar
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
        @cancelSignal="(e) => {inscribe=e; eventSelected={}}"
    />
</template>

<script>
import FullCalendar from "@fullcalendar/vue3";
import dayGridPlugin from "@fullcalendar/daygrid";
import interactionPlugin from "@fullcalendar/interaction"
import ModalEvents from "./subcomponents/modal_event.vue"
import ModalInscript from "./subcomponents/modal_form_inscription.vue"
import eventsService from '@/javascript/axios_events.js';

export default {
    
    data() {
        return {
            seeModalEvent: false,
            inscribe: false,
            eventSelected: {},
            events: []
        }
    },

    async mounted() {
        this.events = await eventsService.getAllEvents();
        this.events = [...this.events]
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
                events: [
                    ...this.eventsList
                ],
                selectable:true,
                eventClick: this.handleSelect,
                locale: 'fr',
                firstDay: 1,
                eventContent: this.renderEvent,
                height: 650
            }
        },
    },
    methods: {
        renderEvent(arg) {
            const title = arg.event.title;
            
            let number = arg.event.extendedProps.nonsubscribePlace;
            if (arg.event.extendedProps.subscribePlace > 0) {
                number += arg.event.extendedProps.subscribePlace
            }
            number = number - arg.event.extendedProps.users.length;
            
            const hour = arg.timeText
            const wrapper = document.createElement('div');
            wrapper.innerHTML = `<p>${hour}</p><p><b>${title}</b></p><p><small>${number} slots available</small></p>`;

            return { domNodes: [wrapper] };
        },

        handleSelect(e){
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
