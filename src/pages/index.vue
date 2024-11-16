<script setup lang="ts">
import useAuthStore from '@/stores/authStore'
import { storeToRefs } from 'pinia'

const { isAuthenticated, currentRole } = storeToRefs(useAuthStore())
</script>

<template>
    <div>
        <h1>Bédé Primée 🗣️🗣️🔊🔊</h1>
        <template v-if="isAuthenticated">
            <RouterLink
                v-if="currentRole === 'admin'"
                :to="{ name: 'admin.dashboard' }"
            >
                Admin Dashboard
            </RouterLink>
            <RouterLink
                v-if="currentRole === 'user'"
                :to="{ name: 'user.dashboard' }"
            >
                User Dashboard
            </RouterLink>
        </template>
        <template v-else>
            <RouterLink :to="{ name: 'auth.login' }">
                Connexion
            </RouterLink><br>
            <RouterLink :to="{ name: 'auth.register' }">
                Inscription
            </RouterLink><br>
        </template>
    </div>
</template>
