import { createApp } from 'vue'
import './assets/main.css'

import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { fas } from '@fortawesome/free-solid-svg-icons'
import { far } from '@fortawesome/free-regular-svg-icons'
import { fab } from '@fortawesome/free-brands-svg-icons'

library.add(fas, far, fab)

document.querySelectorAll('.vue-app').forEach(async (el) => {
    const componentName = el.dataset.component

    try {
        const module = await import(`./components/${componentName}.vue`)
        const app = createApp(module.default)

        app.component('font-awesome-icon', FontAwesomeIcon)
        app.mount(el)
    } catch (err) {
        console.error(`Component ${componentName} not found`)
    }
})
