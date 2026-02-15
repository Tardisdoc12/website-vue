import { createApp } from 'vue'
import './assets/main.css'
import { library } from '@fortawesome/fontawesome-svg-core'
import { defineAsyncComponent } from 'vue'
/* import font awesome icon component */
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

/* import icons and add them to the Library */
import { fas } from '@fortawesome/free-solid-svg-icons'
import { far } from '@fortawesome/free-regular-svg-icons'
import { fab } from '@fortawesome/free-brands-svg-icons'



/* add icons to the library */
library.add(fas, far, fab)

const components = {
  login: defineAsyncComponent(() => import('./components/login.vue')),
  calendar: defineAsyncComponent(() => import('./components/calendar.vue')),
  compte: defineAsyncComponent(() => import('./components/global_compte.vue')),
}

document.querySelectorAll('.vue-root').forEach(el => {
  const moduleName = el.dataset.module
  const component = components[moduleName]

  if (component) {
    const app = createApp(component)
    app.component('font-awesome-icon', FontAwesomeIcon)
    app.mount(el)
  }
})
