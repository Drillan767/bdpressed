import useAuthStore from '@/stores/authStore'
import { storeToRefs } from 'pinia'
import { createRouter, createWebHistory } from 'vue-router'
import administration from './administration'
import utilisateur from './utilisateur'
import visitors from './visitors'

const routes = [
    ...administration,
    ...visitors,
    ...utilisateur,
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

router.beforeEach((to) => {
    const { ensureLoggedIn } = useAuthStore()
    const { currentUser } = storeToRefs(useAuthStore())

    if (to.meta.requiresAuth && !ensureLoggedIn()) {
        return '/connexion'
    }

    if ((to.path === '/connexion' || to.path === '/inscription' || to.path === '/oubli-mot-de-passe') && currentUser.value) {
        return currentUser.value.role === 'admin' ? '/administration' : '/utilisateur'
    }

    if (to.meta.requiresRole && currentUser.value?.role !== to.meta.requiresRole) {
        return '/'
    }
})

export default router
