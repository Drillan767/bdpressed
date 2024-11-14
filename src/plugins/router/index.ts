import { createRouter, createWebHistory } from 'vue-router'

import login from '@/pages/login.vue'
import register from '@/pages/register.vue'
import index from '@/pages/index.vue'
import admin from './admin'

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
  ...admin,
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router