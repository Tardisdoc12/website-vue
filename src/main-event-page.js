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

const initVueApp = () => {
    const el = document.getElementById('event-page');
    if (!el) return;

    const postId = el.dataset.postId;
    const app = createApp(EventPage, { postId });
    app.component('font-awesome-icon', FontAwesomeIcon);
    app.mount(el);
};


// Si Elementor est présent
if (typeof elementorFrontend !== 'undefined' && elementorFrontend.hooks) {
    elementorFrontend.hooks.addAction('frontend/element_ready/global', () => {
        initVueApp();
    });
} else {
    // Fallback : attendre que le DOM soit prêt
    document.addEventListener('DOMContentLoaded', initVueApp);
}