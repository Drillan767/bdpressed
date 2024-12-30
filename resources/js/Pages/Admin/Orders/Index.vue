<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useHead } from '@vueuse/head'
import { ref } from 'vue'
import { useDisplay } from 'vuetify'

defineOptions({ layout: AdminLayout })

useHead({
    title: 'Commandes',
})
</script>

<template>
    <h1>Commandes</h1>
    <VDataTable
        :headers="[
            {
                title: 'Commande',
                key: 'id',
                sortable: true,
            },
            {
                title: 'Statut',
                key: 'status',
                sortable: true,
            },
            {
                title: 'Date',
                key: 'created_at',
                sortable: true,
            },
            {
                title: 'Actions',
                key: 'actions',
                sortable: false,
                align: 'end',
            },
        ]"
        :items="[]"
    />
</template>
