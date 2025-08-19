<template>
    <button
        @click="isActive = !isActive"
        class="bg-blue-500 text-white px-4 py-2 rounded"
    >
    Voir le formulaire
    </button>
    <formulaire
        v-if="isActive"
    />
    <calendar
        v-if="!isActive"
    />
    <Login></Login>
</template>

<script>
import formulaire from "./components/formulaire.vue"
import calendar from "./components/calendar.vue"
import Login from "./components/Login.vue"

export default {
    data() {
        return {
            isActive: false,
            events: [],
        }
    },
    
    components: {
        formulaire,
        calendar,
        Login,
    }
}
</script>

<style>

</style>
