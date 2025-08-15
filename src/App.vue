<template>
    <button
        @click="isActive = !isActive"
        class="bg-blue-500 text-white px-4 py-2 rounded"
    >
    Voir le formulaire
    </button>
    <formulaire
        v-if="isActive"
        :cancelBool.sync="isActive"
        @updateCancelBool="(value) => isActive=value"
    />
    <calendar
        v-if="!isActive"
    />
</template>

<script>
import formulaire from "./components/formulaire.vue"
import calendar from "./components/calendar.vue"

export default {
    data() {
        return {
            isActive: false,
            events: [],
        }
    },
    
    components: {
        formulaire,
        calendar
    }
}
</script>

<style>

</style>
