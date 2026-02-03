import { createApp } from 'vue'
import EventPage from "./components/event_page_proper.vue"
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

document.addEventListener('DOMContentLoaded', () => {
    const el = document.getElementById('event-page');
    if (!el) return;

    const postId = el.dataset.postId;
    const app = createApp(EventPage, { postId });
    app.component('font-awesome-icon', FontAwesomeIcon);
    app.mount(el);
});