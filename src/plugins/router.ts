import { createRouter, createWebHistory } from 'vue-router'
import { handleHotUpdate, routes } from 'vue-router/auto-routes'

/* const routes: RouteRecordRaw[] = [
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
] */

const router = createRouter({
    history: createWebHistory(),
    routes,
})

if (import.meta.hot) {
    handleHotUpdate(router)
}

export default router
