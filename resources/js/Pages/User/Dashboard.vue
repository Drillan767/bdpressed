<script setup lang="ts">
import type { DataTableHeader, OrderIndex } from '@/types'
import useNumbers from '@/Composables/numbers'
import useStatus from '@/Composables/status'
import UserLayout from '@/Layouts/UserLayout.vue'
import { router } from '@inertiajs/vue3'
import { useHead } from '@vueuse/head'
import { route } from 'ziggy-js'

interface Props {
    orders: Omit<OrderIndex, 'user' | 'guest'>[]
}

defineOptions({ layout: UserLayout })

defineProps<Props>()

useHead({
    title: 'Historique des commandes',
})

const { getStatus } = useStatus()
const { formatPrice } = useNumbers()

const headers: DataTableHeader[] = [
    {
        title: 'Référence',
        key: 'reference',
    },
    {
        title: 'Statut',
        key: 'status',
        sortable: true,
    },
    {
        title: 'Total',
        key: 'total',
    },
    {
        title: 'Date de commande',
        key: 'created_at',
        sortable: true,
    },
    {
        title: 'Date de mise à jour',
        key: 'updated_at',
        sortable: true,
    },
    {
        title: 'Actions',
        key: 'actions',
        sortable: false,
        align: 'end',
    },
]
</script>

<template>
    <VContainer>
        <VRow>
            <VCol>
                <h1>Historique des commandes</h1>
            </VCol>
        </VRow>
        <VRow>
            <VCol>
                <VCard>
                    <VCardText>
                        <VDataTable
                            :headers="headers"
                            :items="orders"
                            :items-per-page="10"
                        >
                            <template #item.status="{ item }">
                                <VChip v-bind="getStatus(item.status)" />
                            </template>
                            <template #item.total="{ item }">
                                {{ formatPrice(item.total) }}
                            </template>
                            <template #item.actions="{ item }">
                                <div class="d-flex justify-end">
                                    <VBtn
                                        variant="text"
                                        color="blue"
                                        icon="mdi-eye"
                                        @click="router.visit(route('orders.show', { reference: item.reference }))"
                                    />
                                </div>
                            </template>
                        </VDataTable>
                    </VCardText>
                </VCard>
            </VCol>
        </VRow>
    </VContainer>
</template>
