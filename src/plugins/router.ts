import type { RouteRecordRaw } from 'vue-router'
import { createRouter, createWebHistory } from 'vue-router'

const routes: RouteRecordRaw[] = [
    {
        path: '/',
        name: 'home',
        component: () => import('@/pages/index.vue'),
    },
    {
        path: '/login',
        name: 'login',
        component: () => import('@/pages/login.vue'),
    },
    {
        path: '/administration',
        name: 'administration',
        component: () => import('@/pages/administration.vue'),
    },
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

export default router
