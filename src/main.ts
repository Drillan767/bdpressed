import { createApp } from 'vue'
import App from './App.vue'
import router from './plugins/router'
import { Amplify } from 'aws-amplify'
import { createPinia } from 'pinia'
import outputs from '../amplify_outputs.json'

Amplify.configure(outputs)
const pinia = createPinia()

createApp(App)
    .use(pinia)
    .use(router)
    .mount('#app')
