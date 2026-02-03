
<template>
  <div v-if="eventData">
    <h1>{{ eventData.title }}</h1>
    <p>{{ eventData.description }}</p>
  </div>
  <div v-else>Chargement...</div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({
  postId: {
    type: String,
    required: true
  }
})

const eventData = ref(null)

onMounted(async () => {
    console.log('Fetching event data for postId:', props.postId)
    try {
        const res = await axios.get(`/wp-json/vue-plugin/v1/events/${props.postId}`)
        eventData.value = res.data
    } catch (e) {
        console.error(e)
    }
})
</script>