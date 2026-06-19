<template>
    <div
        class="background-card"
        :style="{
            'background-color': backgroundColorCard,
            'border': '1px solid ' + backgroundColor
        }"
        @click="OnClickEventCard"
    >
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
                <div v-if="isShow">
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
        }
    },

    methods: {
        OnClickEventCard() {
            this.$emit('click-event-card', this.event)
        }
    }
}
</script>