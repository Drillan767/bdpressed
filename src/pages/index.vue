<script setup lang="ts">
import useAuthStore from '@/stores/authStore'
import { storeToRefs } from 'pinia'

definePage({
    meta: {
        title: 'Bédé Primée 🗣️🗣️🔊🔊',
        requiresAuth: false,
    },
})

const { isAuthenticated, currentUser } = storeToRefs(useAuthStore())
</script>

<template>
    <div>
        <h1>Bédé Primée 🗣️🗣️🔊🔊</h1>

        <p v-if="currentUser">
            {{ currentUser }}
        </p>
        <template v-if="isAuthenticated">
            <RouterLink
                v-if="currentUser?.role === 'admin'"
                to="/administration"
            >
                Admin Dashboard
            </RouterLink>
            <RouterLink
                v-if="currentUser?.role === 'user'"
                to="/utilisateur"
            >
                User Dashboard
            </RouterLink>
        </template>
        <template v-else>
            <RouterLink to="/connexion">
                Connexion
            </RouterLink><br>
            <RouterLink to="/inscription">
                Inscription
            </RouterLink><br>
        </template>
    </div>
</template>
