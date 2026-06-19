<template>
    <div
        class="background-card"
        :style="{
            'background-color': backgroundColorCard,
            'border': '1px solid ' + backgroundColor,
            'position': 'relative',
        }"
        @click="OnClickEventCard"
    >

         <!-- Badges -->
        <div style="position: absolute; top: 6px; right: 4px; display: flex; flex-direction: column; gap: 3px; align-items: center;">
            <div
                v-if="isUpdated"
                title="Événement modifié"
                style="
                    width: 18px; height: 18px;
                    border-radius: 50%;
                    background-color: orange;
                    color: white;
                    font-size: 11px;
                    font-weight: bold;
                    display: flex; align-items: center; justify-content: center;
                "
            >!</div>
        </div>

        <div class="event-row">
            <div
                class="event-card"
                :style="{ 'background-color': backgroundColor }"
            ></div>
            <div class="event-content" :style="{ 
                color: colorWriting,
                overflow: 'hidden',
                'min-width': '0',
            }">
                <span class="event-font" style="font-size: 15px;">
                    {{ hour }}
                </span>
                <div>
                    <b class="event-font" style="display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ title }}</b>
                </div>
                <small v-if="isShow" class="event-font">{{ place }}</small>
                <div v-if="isShow" style="display: flex; align-items: center; gap: 4px;">
                    <div
                        v-if="isInscrit"
                        title="Vous êtes inscrit"
                        style="
                            width: 13px; height: 13px;
                            border-radius: 50%;
                            background-color: #22c55e;
                            color: white;
                            font-size: 11px;
                            display: flex; align-items: center; justify-content: center;
                        "
                    >✓</div>
                    <small class="event-font"><i>{{ placesAvailable }}</i></small>
                </div>
                <p v-if="isEncadrant" class="event-font">
                    {{ users.length }} inscrits
                </p>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        event: {
            type: Object,
            required: true,
        },
        hour: {
            type: String,
            required: false,
            default: ""
        },
        title: {
            type: String,
            required: false,
            default: ""
        },
        place: {
            type: String,
            required: false,
            default: ""
        },
        placesAvailable: {
            type: String,
            required: false,
            default: ""
        },
        backgroundColor: {
            type: String,
            required: false,
            default: "rgba(211, 211, 211, 1)"
        },
        backgroundColorCard: {
            type: String,
            required: false,
            default: "rgba(211, 211, 211, 0.2)"
        },
        colorWriting: {
            type: String,
            required: false,
            default: "rgba(0, 0, 0, 1)"
        },
        isEncadrant: {
            type: Boolean,
            required: false,
            default: false
        },
        users: {
            type: Array,
            required: false,
            default: () => []
        },

        isShow:{
            type: Boolean,
            required: false,
            default: true
        },
        userConnected: {
            type: Object,
            required: false,
            default: () => ({})
        },
        isInscrit: {
            type: Boolean,
            required: false,
            default: false
        }
    },

    computed: {
        isUpdated() {
            if (this.isInscrit) {
                console.log("userEvents:", this.event.users)
                console.log("userConnected:", this.userConnected)
                const updateDate = this.event.update_date ? new Date(this.event.update_date) : null
                const userInscription = this.event.users?.find(u => u.email === this.userConnected?.email)
                console.log("userInscription:", userInscription)
                const inscritDate = userInscription?.date_inscrit ? new Date(userInscription.date_inscrit) : null
                console.log("updateDate:", updateDate, "inscritDate:", inscritDate)
                const isUpdatedAfterInscription = updateDate && inscritDate && updateDate > inscritDate
                return isUpdatedAfterInscription
            }
            return false
        },
    },

    methods: {
        OnClickEventCard() {
            this.$emit('click-event-card', this.event)
        }
    }
}
</script>