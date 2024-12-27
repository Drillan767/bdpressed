<script setup lang="ts">
import VisitorsLayout from '@/Layouts/VisitorsLayout.vue'
import { useHead } from '@vueuse/head'

defineOptions({ layout: VisitorsLayout })

useHead({
    title: 'Merci !',
})
</script>

<template>
    <h1>Merci pour votre achat !</h1>
</template>
