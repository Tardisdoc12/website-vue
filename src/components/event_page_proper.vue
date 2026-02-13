
<template>
  <div v-if="eventData">
    <h1>{{ eventData.title }}</h1>
    <p style="white-space: pre-line;">{{ eventData.description }}</p>
  </div>
  <div v-else>Chargement...</div>
</template>

<script>
import axiosEvent from '@/javascript/api/axios_events.js'

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
      }
  },

  async mounted() {
      try {
          const res = await axiosEvent.getEventByPostId(this.postId)
          this.eventData = res
      } catch (e) {
          console.error(e)
      }
  },
}
</script>