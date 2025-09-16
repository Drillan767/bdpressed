<script setup lang="ts">
import Business from '@/Components/Admin/Statistics/Business.vue'
import Financial from '@/Components/Admin/Statistics/Financial.vue'
import Stocks from '@/Components/Admin/Statistics/Stocks.vue'
import Customers from '@/Components/Admin/Statistics/Customers.vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useHead } from '@vueuse/head'

defineOptions({ layout: AdminLayout })

useHead({
    title: 'Accueil',
})

</script>

<template>
    <VContainer fluid>
        <h1 class="text-h4 mb-6">
            Tableau de bord
        </h1>

        <Financial />
        <Business />
        <Stocks />
        <Customers />
    </VContainer>
</template>
