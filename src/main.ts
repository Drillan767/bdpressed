import { Amplify } from 'aws-amplify'
import { createApp } from 'vue'
import outputs from '../amplify_outputs.json'
import App from './App.vue'
import './assets/main.css'

Amplify.configure(outputs)

createApp(App).mount('#app')
