<script setup lang="ts">
import UserLayout from '@/layouts/UserLayout.vue'
import { signOut } from 'aws-amplify/auth'
import { useRouter } from 'vue-router'

const router = useRouter()

async function logout() {
    await signOut()
    router.push('/')
}
</script>

<template>
    <UserLayout>
        <h1>User Dashboard</h1>
        <VBtn @click="logout">
            Logout
        </VBtn>
    </UserLayout>
</template>
