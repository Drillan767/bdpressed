import { Amplify } from 'aws-amplify'
import { createPinia } from 'pinia'
import { createApp } from 'vue'
import outputs from '../amplify_outputs.json'
import App from './App.vue'
import router from './plugins/router'
import vuetify from './plugins/vuetify'

Amplify.configure(outputs)
const pinia = createPinia()

createApp(App)
    .use(pinia)
    .use(router)
    .use(vuetify)
    .mount('#app')
