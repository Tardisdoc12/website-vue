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

Object.entries(VUE_SHORTCODES).forEach(([key, value]) => {
   new_components[key] = defineAsyncComponent(() => import(`./components/${value}.vue`))
})

document.querySelectorAll('.vue-root').forEach(el => {
  const moduleName = el.dataset.module
  const component = new_components[moduleName]
  console.log("On veut = ", moduleName, "et ",component)
  if (moduleName === "event-page"){
    const app = createApp(component,{postId: el.dataset.postId})
    app.component('font-awesome-icon', FontAwesomeIcon)
    app.mount(el)
    return
  }

  if (component) {
    const app = createApp(component)
    app.component('font-awesome-icon', FontAwesomeIcon)
    app.mount(el)
  }
})
