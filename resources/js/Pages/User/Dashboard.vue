<script setup lang="ts">
import type { Money, OrderStatus } from '@/types'
import useStatus from '@/Composables/status'
import UserLayout from '@/Layouts/UserLayout.vue'
import { useHead } from '@vueuse/head'
import { route } from 'ziggy-js'

interface IllustrationDetailItem {
    name: string
    price: string
}

interface OrderItem {
    type: 'product' | 'illustration'
    title: string
    description: string
    price: number
    quantity: number
    totalPrice: number
    image?: string
    status: OrderStatus
    illustrationType?: string
    details?: Record<string, IllustrationDetailItem>
}

interface DashboardOrder {
    id: number
    reference: string
    status: OrderStatus
    total: Money
    details_count: number
    illustrations_count: number
    shipmentFees: Money
    stripeFees: Money
    created_at: string
    itemCount: number
    items: OrderItem[]
}

interface Props {
    orders: DashboardOrder[]
}

defineOptions({ layout: UserLayout })

defineProps<Props>()

useHead({
    title: 'Historique des commandes',
})

const { getOrderStatus } = useStatus()
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
                    :title="`#${order.reference}`"
                >
                    <template #subtitle>
                        <div class="d-flex align-center flex-wrap ga-2">
                            <VChip
                                :text="order.created_at"
                                class="text-caption"
                                variant="text"
                                prepend-icon="mdi-calendar-outline"
                            />
                            <VChip
                                :text="`${order.details_count + order.illustrations_count} article(s)`"
                                class="text-caption"
                                variant="text"
                                prepend-icon="mdi-package-variant-closed"
                            />
                            <VChip
                                v-bind="getOrderStatus(order.status)"
                                size="small"
                            />
                        </div>
                    </template>
                    <template #append>
                        <div class="text-right">
                            <div class="text-h6 font-weight-bold">
                                {{ order.total.formatted }}
                            </div>
                        </div>
                    </template>
                    <VCardText v-if="order.items && order.items.length > 0">
                        <div class="d-flex flex-wrap ga-2">
                            <VChip
                                v-for="(item, index) in order.items.slice(0, 3)"
                                :key="index"
                                size="small"
                                variant="outlined"
                                class="text-caption"
                            >
                                {{ item.title }}
                                <span v-if="item.quantity > 1" class="ml-1">x{{ item.quantity }}</span>
                            </VChip>
                            <VChip
                                v-if="order.items.length > 3"
                                size="small"
                                variant="outlined"
                                class="text-caption"
                            >
                                +{{ order.items.length - 3 }} autre(s)
                            </VChip>
                        </div>
                    </VCardText>
                    <template #actions>
                        <VSpacer />
                        <VBtn
                            :href="route('user.order.show', { reference: order.reference })"
                            variant="flat"
                            append-icon="mdi-magnify"
                        >
                            Détails
                        </VBtn>
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
