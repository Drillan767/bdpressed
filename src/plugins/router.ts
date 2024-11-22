import useAuthStore from '@/stores/authStore'
import { storeToRefs } from 'pinia'
import { createRouter, createWebHistory } from 'vue-router'
import { handleHotUpdate, routes } from 'vue-router/auto-routes'

const router = createRouter({
    history: createWebHistory(),
    routes,
})

if (import.meta.hot) {
    handleHotUpdate(router)
}

router.beforeEach((to) => {
    const { ensureLoggedIn } = useAuthStore()
    const { currentUser } = storeToRefs(useAuthStore())

    if (to.meta.requiresAuth && !ensureLoggedIn()) {
        return '/connexion'
    }

    if (to.meta.requiresRole && currentUser.value?.role !== to.meta.requiresRole) {
        return '/'
    }
})

export default router
