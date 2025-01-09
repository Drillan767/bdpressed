<script setup lang="ts">
import type { DataTableHeader, OrderIndex, User } from '@/types'
import useNumbers from '@/Composables/numbers'
import useStatus from '@/Composables/status'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { router } from '@inertiajs/vue3'
import { useHead } from '@vueuse/head'
import { ref } from 'vue'
import { route } from 'ziggy-js'

interface Props {
    orders: OrderIndex[]
    auth: {
        user: User | null
    }
    errors?: Record<string, string>
    csrf_token: string

}

defineOptions({ layout: AdminLayout })

defineProps<Props>()

useHead({
    title: 'Commandes',
})

const { getStatus } = useStatus()
const { formatPrice } = useNumbers()

const search = ref<string>()

const headers: DataTableHeader[] = [
    {
        title: 'Référence',
        key: 'reference',
    },
    {
        title: 'Client',
        key: 'client',
    },
    {
        title: 'Total',
        key: 'total',
    },
    {
        title: 'Statut',
        key: 'status',
        sortable: true,
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
    <h1>Commandes</h1>
    <VDataTable
        :headers
        :search
        :items="orders"
    >
        <template #top>
            <VRow class="mr-2">
                <VCol
                    cols="12"
                    md="3"
                    offset-md="9"
                    class="my-2"
                >
                    <VTextField
                        v-model="search"
                        label="Rechercher"
                        prepend-inner-icon="mdi-magnify"
                        density="compact"
                        hide-details
                    />
                </VCol>
            </VRow>
        </template>
        <template #item.client="{ item }">
            <div v-if="item.guest">
                {{ item.guest.email }}
            </div>
            <div v-else-if="item.user">
                {{ item.user.email }}
            </div>
        </template>
        <template #item.total="{ item }">
            {{ formatPrice(item.total) }}
        </template>
        <template #item.status="{ item }">
            <VChip v-bind="getStatus(item.status)" />
        </template>
        <template #item.actions="{ item }">
            <div class="d-flex justify-end">
                <VBtn
                    variant="text"
                    color="blue"
                    icon="mdi-eye"
                    @click="router.visit(route('user.order.show', { reference: item.reference }))"
                />
            </div>
        </template>
    </VDataTable>
</template>
