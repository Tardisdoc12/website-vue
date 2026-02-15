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

let new_components = {}

VUE_SHORTCODES.forEach(name => {
    new_components[name] = defineAsyncComponent(() => import(`./components/${name}.vue`))
})

document.querySelectorAll('.vue-root').forEach(el => {
  const moduleName = el.dataset.module
  const component = new_components[moduleName]

  if (component) {
    const app = createApp(component)
    app.component('font-awesome-icon', FontAwesomeIcon)
    app.mount(el)
  }
})
