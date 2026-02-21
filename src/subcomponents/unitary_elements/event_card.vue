<template>
    <div
        class="background-card"
        :style="{
            'background-color': `${backgroundColorCard}`,
            'border': '1px solid ' + `${BackgroundColor}`
        }"
    >
        <div class="event-row">
            <div
                class="event-card"
                :style="{
                    'background-color': `${BackgroundColor}`
                }"
            ></div>
            <div class="event-content" :style="{ 'color':'#000000', }">
                <span class="event-font" style="font-size: 15px;">
                    {{ hour }}
                </span>
                <div>
                    <b class="event-font" style="font-size: 20px;">{{ title }}</b>
                </div>
                <p
                    v-if="isEncadrant"
                    class="event-font"
                >
                    {{ `${event.users.length} ` + "inscrits" }}
                </p>
            </div>
        </div>
    </div>
</template>

<script>
import EventsFunctions from '@/javascript/constants/events_functions'

export default{
    props:{
        event:{
            type: Object,
            required: true,
        },

        isEncadrant:{
            type: Boolean,
            required: false,
            default: false
        }
    },

    data() {
        return {
            title: this.event?.title ?? "Title",
            hour: this.event?.startDate ?? "18h",
            places_available: 0,
        }
    },

    computed:{
        ColorsCard() {
            const colors = EventsFunctions.colorBg(this.event.categorie)
            return colors
        },

        BackgroundColor(){
            return this.ColorsCard[0]
        },

        backgroundColorCard() {
            return this.ColorsCard[1]
        },
    }


}
</script>

<style>
</style>