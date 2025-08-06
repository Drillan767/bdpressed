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

const { getOrderStatus } = useStatus()
const { formatPrice } = useNumbers()
</script>

<template>
    <VContainer>
        <VRow>
            <VCol>
                <h1>
                    <VIcon icon="mdi-package-variant" />
                    Historique des commandes
                </h1>
            </VCol>
        </VRow>
        <VRow
            v-for="order in orders"
            :key="order.reference"
        >
            <VCol>
                <VCard
                    :title="`Commande #${order.reference}`"
                >
                    <template #subtitle>
                        <span>
                            <VIcon
                                icon="mdi-calendar-outline"
                                size="small"
                            />
                            {{ order.created_at }}
                        </span>
                        <span>
                            <VICon
                                icon="mdi-package-variant"
                                size="small"
                            />
                            3 articles
                        </span>
                    </template>
                    <template #append>
                        <VChip v-bind="getOrderStatus(order.status)" />
                    </template>
                </VCard>
            </VCol>
        </VRow>
        <VRow v-if="orders.length === 0">
            <VCol>
                <VEmptyState
                    title="Aucune commande trouvée"
                    description="Vous n'avez pas encore passé de commande."
                />
            </VCol>
        </VRow>
    </VContainer>
</template>
