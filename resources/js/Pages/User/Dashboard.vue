<script setup lang="ts">
import UserLayout from '@/Layouts/UserLayout.vue'
import { useHead } from '@vueuse/head'

defineOptions({ layout: UserLayout })

useHead({
    title: 'Profil',
})
</script>

<template>
    <h1>Dashboard</h1>
</template>
