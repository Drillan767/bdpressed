<script setup lang="ts">
import VisitorsLayout from '@/Layouts/VisitorsLayout.vue'
import { useHead } from '@vueuse/head'

defineOptions({ layout: VisitorsLayout })

useHead({
    title: 'Commander une illustration',
})
</script>

<template>
    <h1>Illustration</h1>
</template>
