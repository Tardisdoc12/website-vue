import { createApp } from 'vue'
import Adherent from "./components/adherent.vue"
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

const app = createApp(Adherent)

app.component('font-awesome-icon', FontAwesomeIcon)
app.mount('#form_adhesion')