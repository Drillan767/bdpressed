<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useHead } from '@vueuse/head'

useHead({
    title: 'Accueil',
})

defineOptions({ layout: AdminLayout })
</script>

<template>
    <h1>Admin Dashboard</h1>
    <h2>Ici faut imaginer des petites stats bien stylées et tout</h2>
</template>
