<script setup lang="ts">
import type { Address, OrderDetail } from '@/types'
import useNumbers from '@/Composables/numbers'
import useStatus from '@/Composables/status'
import useStrings from '@/Composables/strings'
import UserLayout from '@/Layouts/UserLayout.vue'
import { useHead } from '@vueuse/head'

interface Props {
    order: Omit<OrderDetail, 'user' | 'guest'> & {
        shipping_address: Address
        billing_address?: Address
    }
}

defineOptions({ layout: UserLayout })

const props = defineProps<Props>()

useHead({
    title: () => `Commande ${props.order.reference}`,
})

const { formatPrice } = useNumbers()
const { getOrderStatus } = useStatus()
const { toParagraphs } = useStrings()
</script>

<template>
    <VContainer>
        <VRow>
            <VCol>
                <h1>
                    Commande {{ order.reference }}
                    <VChip
                        v-bind="getOrderStatus(order.status)"
                        class="ml-2"
                    />
                </h1>
            </VCol>
        </VRow>
        <VRow>
            <VCol>
                <VCard>
                    <template #text>
                        <VContainer>
                            <VRow>
                                <VCol
                                    cols="12"
                                    md="8"
                                >
                                    <template v-if="order.additionalInfos">
                                        <VRow no-gutters>
                                            <VCol>
                                                <p class="font-weight-bold">
                                                    Demande :
                                                </p>
                                                <div v-html="toParagraphs(order.additionalInfos)" />
                                            </VCol>
                                        </VRow>
                                        <VDivider class="my-4" />
                                    </template>
                                    <VRow>
                                        <VCol
                                            cols="12"
                                            md="6"
                                        >
                                            <h2 class="mb-4">
                                                Adresse de livraison
                                            </h2>
                                            <p class="mb-1">
                                                {{ order.shipping_address.firstName }} {{ order.shipping_address.lastName }}
                                            </p>
                                            <p class="mb-1">
                                                {{ order.shipping_address.street }}
                                            </p>
                                            <p
                                                v-if="order.shipping_address.street2.length > 1"
                                                class="mb-1"
                                            >
                                                {{ order.shipping_address.street2 }}
                                            </p>
                                            <p class="mb-1">
                                                {{ order.shipping_address.zipCode }}
                                                {{ order.shipping_address.city }} -
                                                {{ order.shipping_address.country }}
                                            </p>
                                        </VCol>
                                        <VCol
                                            v-if="order.billing_address"
                                            cols="12"
                                            md="6"
                                        >
                                            <h2 class="mb-4">
                                                Adresse de facturation
                                            </h2>
                                            <p class="mb-1">
                                                {{ order.billing_address.firstName }} {{ order.billing_address.lastName }}
                                            </p>
                                            <p class="mb-1">
                                                {{ order.billing_address.street }}
                                            </p>
                                            <p
                                                v-if="order.billing_address.street2.length > 1"
                                                class="mb-1"
                                            >
                                                {{ order.billing_address.street2 }}
                                            </p>
                                            <p class="mb-1">
                                                {{ order.billing_address.zipCode }}
                                                {{ order.billing_address.city }} -
                                                {{ order.billing_address.country }}
                                            </p>
                                        </VCol>
                                    </VRow>
                                </VCol>
                                <VDivider vertical />
                                <VCol
                                    cols="12"
                                    md="4"
                                >
                                    <h2>
                                        Information de la commande
                                    </h2>
                                    <VList>
                                        <template
                                            v-for="(illustration, i) in order.illustrationsList"
                                            :key="i"
                                        >
                                            <VListGroup :value="i">
                                                <template #activator="{ props: illustrationProps }">
                                                    <VListItem
                                                        v-bind="illustrationProps"
                                                        :title="`Illustration (${illustration.price.price})`"
                                                        color="primary"
                                                    />
                                                </template>

                                                <VListItem :title="illustration.type.name">
                                                    <template #append>
                                                        {{ illustration.type.price }}
                                                    </template>
                                                </VListItem>

                                                <VListItem
                                                    v-if="illustration.nbHumans"
                                                    :title="illustration.nbHumans.name"
                                                >
                                                    <template #append>
                                                        {{ illustration.nbHumans.price }}
                                                    </template>
                                                </VListItem>
                                                <VListItem
                                                    v-if="illustration.nbAnimals"
                                                    :title="illustration.nbAnimals.name"
                                                >
                                                    <template #append>
                                                        {{ illustration.nbAnimals.price }}
                                                    </template>
                                                </VListItem>
                                                <VListItem :title="illustration.pose.name">
                                                    <template #append>
                                                        {{ illustration.pose.price }}
                                                    </template>
                                                </VListItem>
                                                <VListItem :title="illustration.background.name">
                                                    <template #append>
                                                        {{ illustration.background.price }}
                                                    </template>
                                                </VListItem>
                                                <VListItem
                                                    v-if="illustration.addTracking"
                                                    :title="illustration.addTracking.name"
                                                >
                                                    <template #append>
                                                        {{ illustration.addTracking.price }}
                                                    </template>
                                                </VListItem>
                                                <VListItem
                                                    v-if="illustration.print"
                                                    :title="illustration.print.name"
                                                >
                                                    <template #append>
                                                        {{ illustration.print.price }}
                                                    </template>
                                                </VListItem>
                                            </VListGroup>
                                        </template>
                                        <VListItem
                                            v-for="(detail, i) in order.details"
                                            :key="i"
                                            :title="`${detail.product.name} x ${detail.quantity}`"
                                            :prepend-avatar="detail.product.promotedImage"
                                        >
                                            <template #append>
                                                {{ formatPrice(detail.price) }}
                                            </template>
                                        </VListItem>
                                        <VDivider />
                                        <VListItem
                                            title="Frais de paiement"
                                        >
                                            <template #append>
                                                {{ formatPrice(order.stripeFees) }}
                                            </template>
                                        </VListItem>
                                        <VDivider />
                                        <VListItem
                                            title="Frais de port (estimés)"
                                        >
                                            <template #append>
                                                {{ formatPrice(order.shipmentFees) }}
                                            </template>
                                        </VListItem>
                                        <VDivider />
                                        <VListItem>
                                            <template #title>
                                                <b>Total</b>
                                            </template>
                                            <template #append>
                                                <b>{{ formatPrice(order.total) }}</b>
                                            </template>
                                        </VListItem>
                                    </VList>
                                </VCol>
                            </VRow>
                        </VContainer>
                    </template>
                </VCard>
                <!-- <VCard>
                    <template #text>
                        <VContainer>
                            <VRow>
                                <VCol
                                    cols="12"
                                    md="4"
                                >
                                    <h2>
                                        Information de la commande
                                    </h2>
                                    <VList>
                                        <VListItem
                                            v-for="(detail, i) in order.details"
                                            :key="i"
                                            :title="`${detail.product.name} x ${detail.quantity}`"
                                            :prepend-avatar="detail.product.promotedImage"
                                        >
                                            <template #append>
                                                {{ formatPrice(detail.price) }}
                                            </template>
                                        </VListItem>
                                        <VDivider />
                                        <VListItem
                                            title="Frais de paiement"
                                        >
                                            <template #append>
                                                {{ formatPrice(order.stripeFees) }}
                                            </template>
                                        </VListItem>
                                        <VDivider />
                                        <VListItem
                                            title="Frais de port (estimés)"
                                        >
                                            <template #append>
                                                {{ formatPrice(order.shipmentFees) }}
                                            </template>
                                        </VListItem>
                                        <VDivider />
                                        <VListItem>
                                            <template #title>
                                                <b>Total</b>
                                            </template>
                                            <template #append>
                                                <b>{{ formatPrice(order.total) }}</b>
                                            </template>
                                        </VListItem>
                                    </VList>
                                </VCol>
                            </VRow>
                        </VContainer>
                    </template>
                </VCard> -->
            </VCol>
        </VRow>
    </VContainer>
</template>
