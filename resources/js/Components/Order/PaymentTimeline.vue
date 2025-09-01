<script setup lang="ts">
import type { PaymentHistory } from '@/types'
import useStatus from '@/Composables/status'

defineProps<{
    payments: PaymentHistory[]
    interactive?: boolean
}>()

const { getPaymentType } = useStatus()
</script>

<template>
    <VCard title="Historique des paiements">
        <template #text>
            <VTimeline
                v-if="payments.length > 0"
                side="end"
                density="compact"
            >
                <VTimelineItem
                    v-for="payment in payments"
                    :key="payment.id"
                    :dot-color="payment.status === 'pending' ? 'orange' : 'success'"
                    size="small"
                    width="100%"
                >
                    <VCard
                        variant="tonal"
                        density="compact"
                    >
                        <template #title>
                            <span class="text-caption text-medium-emphasis">
                                {{ payment.paid_at || 'En attente' }}
                            </span>
                        </template>
                        <template #append>
                            <VChip
                                v-bind="getPaymentType(payment.type)"
                            />
                        </template>
                        <template #text>
                            <span class="font-weight-medium">
                                {{ payment.title }}
                            </span>

                            <VChip
                                size="small"
                                :color="payment.status === 'pending' ? 'orange' : 'success'"
                                :text="payment.amount"
                                class="ml-2"
                            />
                        </template>
                        <template #actions>
                            <VChip
                                size="small"
                                variant="flat"
                                :color="payment.status === 'pending' ? 'orange' : 'success'"
                                :text="payment.status === 'pending' ? 'En attente' : 'Payé'"
                            />

                            <VSpacer />

                            <VBtn
                                v-if="payment.payment_link && interactive"
                                :href="payment.payment_link"
                                target="_blank"
                                color="primary"
                                variant="outlined"
                                size="small"
                                prepend-icon="mdi-credit-card"
                            >
                                Payer
                            </VBtn>
                        </template>
                    </VCard>
                </VTimelineItem>
            </VTimeline>
            <div
                v-else
                class="text-center py-8 text-medium-emphasis"
            >
                <VIcon
                    icon="mdi-credit-card-outline"
                    size="48"
                    class="mb-2"
                />
                <p>Aucun paiement pour le moment</p>
            </div>
        </template>
    </VCard>
</template>
