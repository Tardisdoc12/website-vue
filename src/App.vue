<template>
  <Login v-if="hasLogin" />
  <Calendar v-if="hasCalendar" />
  <Compte v-if="hasCompte" />
</template>

<script setup>
// import Login from './components/login.vue'
// import Calendar from './components/calendar.vue'
// import Compte from './components/global_compte.vue'
import { defineAsyncComponent } from 'vue'

const modules = window.vueAppData?.modules || []

const hasLogin = modules.includes('login')
const hasCalendar = modules.includes('calendar')
const hasCompte = modules.includes('compte')

const Login = defineAsyncComponent({
  loader: () => import('./components/login.vue'),
  delay: 200,
  timeout: 10000
})

const Compte = defineAsyncComponent({
  loader: () => import('./components/global_compte.vue'),
  delay: 200,
  timeout: 10000
})

const Calendar = defineAsyncComponent({
  loader: () => import('./components/calendar.vue'),
  delay: 200,
  timeout: 10000
})
</script>
