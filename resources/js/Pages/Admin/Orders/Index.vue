<script setup lang="ts">
import type { DataTableHeader, OrderIndex, User } from '@/types'
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

const { getOrderStatus } = useStatus()

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
    <h1 class="mb-4">
        Commandes
    </h1>
    <VCard>
        <template #text>
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
                    <VTooltip
                        v-if="item.guest"
                        location="top"
                        class="mr-2"
                    >
                        <template #activator="{ props: tooltip }">
                            <VIcon
                                v-bind="tooltip"
                                color="primary"
                                icon="mdi-account-clock"
                            />
                        </template>

                        La commande a été faite par un invité.
                    </VTooltip>

                    <VTooltip
                        v-else
                        location="top"
                        class="mr-2"
                    >
                        <template #activator="{ props: tooltip }">
                            <VIcon
                                v-bind="tooltip"
                                color="primary"
                                icon="mdi-account-check"
                            />
                        </template>

                        La commande a été faite par un membre inscrit.
                    </VTooltip>

                    {{ item.guest ? item.guest.email : item.user?.email }}
                </template>
                <template #item.total="{ item }">
                    {{ item.total.formatted }}
                </template>
                <template #item.status="{ item }">
                    <VChip v-bind="getOrderStatus(item.status)" />
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
        </template>
    </VCard>
</template>
