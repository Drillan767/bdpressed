import { createRouter, createMemoryHistory } from 'vue-router'

import login from '@/pages/login.vue'
import register from '@/pages/register.vue'
import index from '@/pages/index.vue'
import admin from '@/pages/admin.vue'

const routes = [
  {
    path: '/',
    name: 'index',
    component: index
  },
  {
    path: '/login',
    name: 'login',
    component: login
  },
  {
    path: '/register',
    name: 'register',
    component: register
  },
  {
    path: '/admin',
    name: 'admin',
    component: admin
  }
]

const router = createRouter({
  history: createMemoryHistory(),
  routes
})

console.log()

export default router