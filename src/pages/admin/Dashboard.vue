<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue'
import { signOut } from 'aws-amplify/auth'
import { useRouter } from 'vue-router'

const router = useRouter()

async function logout() {
    await signOut()
    router.push('/')
}
</script>

<template>
    <AdminLayout>
        <h1>Admin Dashboard</h1>
        <VBtn @click="logout">
            Logout
        </VBtn>
    </AdminLayout>
</template>
