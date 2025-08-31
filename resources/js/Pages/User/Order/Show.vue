<script setup lang="ts">
import type { Address, Money } from '@/types'
import type { IllustrationStatus, OrderStatus } from '@/types/enums'
import useNumbers from '@/Composables/numbers'
import useStatus from '@/Composables/status'
import useStrings from '@/Composables/strings'
import UserLayout from '@/Layouts/UserLayout.vue'
import { useHead } from '@vueuse/head'
import { ref } from 'vue'
import { route } from 'ziggy-js'
import PaymentHistory from './PaymentHistory.vue'

interface IllustrationDetail {
    name: string
    price: Money
}

interface ArticleItem {
    type: 'product'
    id: number
    title: string
    description: string
    price: Money
    quantity: number
    totalPrice: number
    image: string
}

interface IllustrationItem {
    type: 'illustration'
    id: number
    title: string
    description: string
    price: number
    image: string
    totalPrice: number
    details: Record<string, IllustrationDetail>
    status: IllustrationStatus
}

interface OrderDetail {
    id: number
    total: Money
    shipmentFees: Money
    reference: string
    estimatedFees: number
    addionalInfos?: string
    created_at: string
    shippingAddress: Address
    billingAddress: Address
    status: OrderStatus
    items: (ArticleItem | IllustrationItem)[]
}

interface Props {
    order: OrderDetail
}

defineOptions({ layout: UserLayout })

const props = defineProps<Props>()

useHead({
    title: () => `Commande ${props.order.reference}`,
})

const { formatPrice } = useNumbers()
const { getOrderStatus, getIllustrationStatus } = useStatus()
const { toParagraphs } = useStrings()

const illustrationDetails = ref<IllustrationItem['details']>()
const illustrationPrice = ref()
const openDetails = ref(false)

function openIllustrationDetails(illustration: IllustrationItem) {
    illustrationDetails.value = illustration.details
    illustrationPrice.value = formatPrice(illustration.totalPrice)
    openDetails.value = true
}
</script>

<template>
    <VContainer>
        <VRow>
            <VCol
                cols="12"
                class="d-flex ga-2"
            >
                <VBtn
                    :href="route('user.dashboard')"
                    icon="mdi-arrow-left"
                    variant="flat"
                />
                <h1>
                    Commande #{{ order.reference }}
                </h1>
            </VCol>
            <VCol cols="12">
                <VIcon
                    icon="mdi-calendar-outline"
                    size="small"
                />
                <span class="text-caption">
                    Passée le {{ order.created_at }}
                </span>
                <VChip
                    v-bind="getOrderStatus(order.status)"
                    size="small"
                    class="ml-md-2"
                />
            </VCol>
        </VRow>
        <VRow>
            <VCol
                cols="12"
                md="8"
            >
                <VCard
                    prepend-icon="mdi-package-variant-closed"
                    title="Articles"
                    class="mb-4"
                >
                    <template #text>
                        <VRow
                            v-for="(item, index) in order.items"
                            :key="index"
                        >
                            <VCol>
                                <VCard variant="outlined">
                                    <VRow no-gutters>
                                        <VCol cols="auto">
                                            <VImg
                                                :src="item.image"
                                                :alt="item.title"
                                                width="120"
                                                height="120"
                                                rounded="lg"
                                                cover
                                                class="ma-3"
                                            />
                                        </VCol>
                                        <VCol>
                                            <VCardText class="pb-2">
                                                <div class="d-flex justify-space-between align-start mb-2">
                                                    <div>
                                                        <h3 class="text-h6 mb-1">
                                                            {{ item.title }}
                                                        </h3>
                                                        <p class="text-body-2 text-medium-emphasis mb-2">
                                                            {{ item.description }}
                                                        </p>
                                                        <div class="d-flex align-center gap-2 mb-2">
                                                            <VChip
                                                                v-if="item.type === 'product'"
                                                                size="small"
                                                                variant="outlined"
                                                                prepend-icon="mdi-package-variant"
                                                            >
                                                                {{ item.quantity }} x {{ item.price.formatted }}
                                                            </VChip>
                                                            <VChip
                                                                v-else
                                                                size="small"
                                                                variant="outlined"
                                                                prepend-icon="mdi-palette"
                                                            >
                                                                Illustration
                                                            </VChip>
                                                            <VChip
                                                                v-if="item.type === 'illustration'"
                                                                v-bind="getIllustrationStatus(item.status)"
                                                                size="small"
                                                            />
                                                        </div>
                                                    </div>
                                                    <div class="text-right">
                                                        <div class="font-weight-bold mb-2">
                                                            {{ formatPrice(item.totalPrice) }}
                                                        </div>
                                                        <VBtn
                                                            v-if="item.type === 'illustration'"
                                                            size="small"
                                                            variant="outlined"
                                                            prepend-icon="mdi-eye"
                                                            @click="openIllustrationDetails(item)"
                                                        >
                                                            Voir détails
                                                        </VBtn>
                                                    </div>
                                                </div>
                                            </VCardText>
                                        </VCol>
                                    </VRow>
                                </VCard>
                            </VCol>
                        </VRow>
                    </template>
                </VCard>
                <VCard
                    v-if="order.addionalInfos"
                    title="Requête spéciale"
                    prepend-icon="mdi-comment-outline"
                >
                    <template #text>
                        <div v-html="toParagraphs(order.addionalInfos)" />
                    </template>
                </VCard>
            </VCol>
            <VCol
                cols="12"
                md="4"
            >
                <VRow>
                    <VCol>
                        <VCard
                            title="Résumé de la commande"
                            prepend-icon="mdi-credit-card-outline"
                        >
                            <template #text>
                                <VList>
                                    <VListItem
                                        title="Sous-total"
                                    >
                                        <template #append>
                                            {{ order.total.formatted }}
                                        </template>
                                    </VListItem>
                                    <VListItem
                                        title="Frais de port"
                                    >
                                        <template #append>
                                            {{ formatPrice(order.estimatedFees / 100) }}
                                        </template>
                                    </VListItem>
                                    <VDivider />
                                    <VListItem class="font-weight-bold">
                                        <template #title>
                                            <b>Total</b>
                                        </template>
                                        <template #append>
                                            <b>
                                                {{ formatPrice(order.total.euros + order.estimatedFees / 100) }}
                                            </b>
                                        </template>
                                    </VListItem>
                                </VList>
                            </template>
                        </VCard>
                    </VCol>
                </VRow>
                <VRow>
                    <VCol>
                        <VCard
                            title="Adresse de livraison"
                            prepend-icon="mdi-map-marker-outline"
                        >
                            <template #text>
                                <p>
                                    {{ order.shippingAddress.firstName }} {{ order.shippingAddress.lastName }}
                                </p>
                                <p>
                                    {{ order.shippingAddress.street }}
                                </p>
                                <p
                                    v-if="order.shippingAddress.street2.length > 1"
                                    class="mb-1"
                                >
                                    {{ order.shippingAddress.street2 }}
                                </p>
                                <p>
                                    {{ order.shippingAddress.zipCode }}
                                    {{ order.shippingAddress.city }} -
                                    {{ order.shippingAddress.country }}
                                </p>
                            </template>
                        </VCard>
                    </VCol>
                </VRow>
                <VRow>
                    <VCol>
                        <VCard
                            title="Adresse de facturation"
                            prepend-icon="mdi-receipt-text-outline"
                        >
                            <template #text>
                                <p>
                                    {{ order.billingAddress.firstName }} {{ order.billingAddress.lastName }}
                                </p>
                                <p>
                                    {{ order.billingAddress.street }}
                                </p>
                                <p
                                    v-if="order.billingAddress.street2.length > 1"
                                    class="mb-1"
                                >
                                    {{ order.billingAddress.street2 }}
                                </p>
                                <p>
                                    {{ order.billingAddress.zipCode }}
                                    {{ order.billingAddress.city }} -
                                    {{ order.billingAddress.country }}
                                </p>
                            </template>
                        </VCard>
                    </VCol>
                </VRow>
                <VRow>
                    <VCol>
                        <PaymentHistory />
                    </VCol>
                </VRow>
            </VCol>
        </VRow>
        <VDialog
            v-model="openDetails"
            width="600"
        >
            <VCard title="Détails de l'illustration">
                <template #text>
                    <VList>
                        <VListItem
                            v-for="(detail, key) in illustrationDetails"
                            :key="key"
                            :title="detail.name"
                        >
                            <template #append>
                                {{ detail.price }}
                            </template>
                        </VListItem>
                        <VDivider />
                        <VListItem
                            class="font-weight-bold"
                            title="Prix total"
                        >
                            <template #append>
                                {{ illustrationPrice }}
                            </template>
                        </VListItem>
                    </VList>
                </template>
                <template #actions>
                    <VSpacer />
                    <VBtn
                        color="primary"
                        variant="text"
                        @click="openDetails = false"
                    >
                        Fermer
                    </VBtn>
                </template>
            </VCard>
        </VDialog>
    </VContainer>
</template>
