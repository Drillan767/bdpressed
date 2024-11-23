import { Amplify } from 'aws-amplify'
import { createPinia } from 'pinia'
import piniaPersistedState from 'pinia-plugin-persistedstate'
import { createApp } from 'vue'
import outputs from '../amplify_outputs.json'
import App from './App.vue'
import router from './plugins/router'
import vuetify from './plugins/vuetify'
import './plugins/vee-validate'
import 'vue3-toastify/dist/index.css'

Amplify.configure(outputs)

const pinia = createPinia()
pinia.use(piniaPersistedState)

createApp(App)
    .use(pinia)
    .use(vuetify)
    .use(router)
    .mount('#app')
