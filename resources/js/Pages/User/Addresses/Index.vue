<script setup lang="ts">
import type { Address } from '@/types'
import UserLayout from '@/Layouts/UserLayout.vue'
import { useHead } from '@vueuse/head'

defineOptions({ layout: UserLayout })

defineProps<{
    addresses: Address[]
}>()

useHead({
    title: 'Mes adresses',
})
</script>

<template>
    <h1>Mes adresses</h1>
</template>
