<script setup lang="ts">
import { onMounted } from 'vue'
import useAuthStore from './stores/authStore'

const { setAuthWatch } = useAuthStore()

onMounted(setAuthWatch)
</script>

<template>
    <RouterView />
</template>
