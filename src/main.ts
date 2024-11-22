import { createApp } from 'vue'
import App from './App.vue'
import router from './plugins/router'

import 'vue3-toastify/dist/index.css'

createApp(App)
    .use(router)
    .mount('#app')
