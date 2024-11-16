import useAuthStore from '@/stores/authStore'
import { storeToRefs } from 'pinia'
import { createRouter, createWebHistory } from 'vue-router'
import admin from './admin'
import auth from './auth'
import user from './user'

const routes = [
    {
        path: '/',
        name: 'index',
        component: () => import('@/pages/index.vue'),
    },
    ...auth,
    ...admin,
    ...user,
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

router.beforeEach((to) => {
    const destinationRoute = to.name?.toString() ?? ''

    const { isAuthenticated, currentRole } = storeToRefs(useAuthStore())

    if (destinationRoute.startsWith('admin.') || destinationRoute.startsWith('user.')) {
        if (!isAuthenticated) {
            return '/connexion'
        }

        if (destinationRoute.startsWith('admin.') && currentRole.value !== 'admin') {
            return '/'
        }

        if (destinationRoute.startsWith('user.') && currentRole.value !== 'user') {
            return '/'
        }
    }

    if (destinationRoute.startsWith('auth.') && isAuthenticated && currentRole) {
        return currentRole.value === 'admin' ? '/admin' : '/utilisateur'
    }
})

export default router
