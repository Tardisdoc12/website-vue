
<template>
    <div v-if="eventData">
        <h1>{{ eventData.title }}</h1>
        <div :style="{padding:'0px 20px'}">
            <button
                v-if="stepsToPass !== 0"
                class="mt-4 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
                @click="ReturnToStart"
            >
                {{ "Retour en arrière" }}
            </button>
        </div>
        <EventPipeline
            :event="eventData"
            :user-connected="user"
            :steps-to-start="stepsToPass"
            :can-be-redirected="false"
            @incrementSteps="incrementSteps"
            @cancel-signal="ReturnToStart"
            @inscritEvent="InscritEvent"
            @userDeleted="userToDelete"
            @deletedEvent="deletedEvent"
        />
    </div>
    <div v-else>Chargement...</div>
</template>

<script>
import axiosEvent from '@/javascript/api/axios_events.js'
import EventPipeline from '@/subcomponents/event_inscribe/event_pipeline.vue'
import EventsFunctions from '@/javascript/constants/events_functions'

export default {
    props:{
        postId: {
            type: String,
            required: true
        }
    },

    data() {
        return {
            eventData: [],
            user: {},
            steps: 0,
        }
    },

    async mounted() {
        try {
            const res = await axiosEvent.getEventByPostId(this.postId)
            this.eventData = {...res, event_id: res.id}
            this.user = await EventsFunctions.isUserConnected()
            if (this.eventData.users.filter(user => Number(user.wp_user_id == Number(this.user.ID)))){
                this.user.events = [{event_id:this.eventData.event_id}]
            }
            this.eventData.isInscript = this.isInscript
        } catch (e) {
            console.error(e)
        }
    },

    computed:{
        isInscript(){
            const thisUserIsInEvents = this.user.events?.some(obj => Number(obj.event_id) === Number(this.eventData.event_id)) ?? false
            const thisEventHasUSer = this.eventData.users?.some(user => Number(user.wp_user_id) === Number(this.user.ID)) ?? false
            return (thisUserIsInEvents && thisEventHasUSer)
        },
        stepsToPass(){
            return this.steps
        }
    },
    methods:{
        ReturnToStart(){
            this.steps = 0
        },

        incrementSteps(steps) {
            this.steps = steps
        },

        async InscritEvent(event){
            const event_id = event?.event_id ? event.event_id : event.id
            this.user.events.push({
                event_id: event_id
            })
            const res = await axiosEvent.getEventByPostId(this.postId)
            this.eventData = {...res, event_id: res.id}
            this.eventData.isInscript = this.isInscript
        },

        deletedEvent(_e){
            this.eventData = {}
        },

        userToDelete(user) {
            const event_id = this.eventData?.event_id ? this.eventData.event_id : this.eventData.id
            this.eventData.users = this.eventData.users.filter(user_ => Number(user_.id) !== Number(user.id))
            if(this?.user && user?.wp_user_id){
                if(Number(user.wp_user_id) == Number(this.user.ID)){
                    const index = this.user.events.findIndex(event => Number(event.event_id) === Number(event_id))
                    if (index !== -1) {
                        this.user.events.splice(index, 1);
                    }
                }
            }
            this.eventData.isInscript = this.isInscript
        }
    },

    components:{
        EventPipeline
    }
}
</script>