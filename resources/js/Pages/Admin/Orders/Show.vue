<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useHead } from '@vueuse/head'
import { ref } from 'vue'
import { useDisplay } from 'vuetify'

defineOptions({ layout: AdminLayout })

useHead({
    title: 'Commande',
})
</script>

<template>
    <h1>Commande</h1>
</template>
