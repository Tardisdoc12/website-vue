import { createApp } from 'vue'
import Account from "./components/global_compte.vue"
import './assets/main.css'
import { library } from '@fortawesome/fontawesome-svg-core'

/* import font awesome icon component */
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

/* import icons and add them to the Library */
import { fas } from '@fortawesome/free-solid-svg-icons'
import { far } from '@fortawesome/free-regular-svg-icons'
import { fab } from '@fortawesome/free-brands-svg-icons'



/* add icons to the library */
library.add(fas, far, fab)

const app = createApp(Account)

app.component('font-awesome-icon', FontAwesomeIcon)
app.mount('#account')