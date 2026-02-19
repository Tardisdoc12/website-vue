<template>
    <CalendarModule
        :eventsList="eventsList"
        :userConnected="user"
        :allowedCreateEvent="allowedCreateEvent"
        @createEvent="StartCreateEvent"
        @eventSelect="SelectEvent"
    />
    <ModalCreateEvent
        v-if="startCreateEvent"
        @cancelSignal="startCreateEvent=false"
        @createEvents="AddEventCreated"
        :onSuccess="creationSuccess"
    />

    <ModalEventInscription
        v-if="seeModalEvent"
        :event="eventSelected"
        :userConnected="user"
        @cancelSignal="closeEvent"
        @userDeleted="userToDelete"
        @deletedEvent="DeleteEvent"
        @inscritEvent="InscritEvent"
    />
</template>

<script>
import CalendarModule from "@/subcomponents/unitary_elements/calendrier_component.vue"
import ModalCreateEvent from "@/subcomponents/modals/modal_formulaire_events.vue"
import ModalEventInscription from "@/subcomponents/modals/modal_events.vue"
import eventsService from '@/javascript/api/axios_events.js';
import { jwtDecode } from "jwt-decode"
import api from "@/javascript/api/users_wp.js"

export default {
    
    data() {
        return {
            user: {},
            seeModalEvent: false,
            startCreateEvent:false,
            allowedCreateEvent: false,
            eventSelected: {},
            events: [],
        }
    },

    async mounted() {
        this.events = await eventsService.getAllEvents();
        const token = sessionStorage.getItem("mps_moto")
        if (token) {
            const decoded = jwtDecode(token)
            const user_id = decoded.data.user.id
            const user_info = await api.get_user(user_id)
            this.user = {...user_info.user}

            const eventsInscript = await eventsService.getEventUser(user_id, this.user.email)
            this.user.events = eventsInscript.results
            const listB = this.user.roles
            const listA = ['bureau', 'administrator']
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
            })
        },
    },
    methods: {
        AddEventCreated(event){
            this.events.push(
                {
                    start: event.startDate,
                    end: event.endDate || event.startDate,
                    event_id:event.id,
                    backgroundColor: 'transparent',
                    borderColor: 'transparent',
                    ...event
                }
            )
        },
        async InscritEvent(event){
            const event_id = event?.event_id ? event.event_id : event.id
            this.user.events.push({
                event_id: event_id
            })
            this.events = await eventsService.getAllEvents();
        },
        DeleteEvent(event_id){
            this.events = this.events.filter(event => Number(event.id) !== Number(event_id))
        },
        SelectEvent(event){
            this.eventSelected = event
            this.seeModalEvent = true
        },

        StartCreateEvent() {
            this.startCreateEvent = true
        },
        
        async closeEvent(e) {
            this.seeModalEvent=e;
            this.eventSelected={};
            this.events = await eventsService.getAllEvents();
        },

        async creationSuccess() {
            this.startCreateEvent=false
            this.events = await eventsService.getAllEvents();
        },

        userToDelete(user) {
            const event_id = this.eventSelected?.event_id ? this.eventSelected.event_id : this.eventSelected.id
            this.eventSelected = this.eventSelected.users.filter(user_ => Number(user_.id) !== Number(user.id))
            if(this?.user && user?.wp_user_id){
                if(Number(user.wp_user_id) == Number(this.user.ID)){
                    const index = this.user.events.findIndex(event => Number(event.event_id) === Number(event_id))
                    if (index !== -1) {
                        this.user.events.splice(index, 1);
                    }
                }
            }
        }
    },
    components: {
        CalendarModule,
        ModalCreateEvent,
        ModalEventInscription
    },
};
</script>

<style>
</style>
