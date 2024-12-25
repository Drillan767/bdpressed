<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useHead } from '@vueuse/head'

defineOptions({ layout: AdminLayout })

useHead({
    title: 'Accueil',
})
</script>

<template>
    <h1>Admin Dashboard</h1>
    <h2>Ici faut imaginer des petites stats bien stylées et tout</h2>
</template>
