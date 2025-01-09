<script setup lang="ts">
import type { OrderDetail } from '@/types'
import useNumbers from '@/Composables/numbers'
import useStatus from '@/Composables/status'
import useStrings from '@/Composables/strings'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useHead } from '@vueuse/head'
import { computed, ref } from 'vue'

interface Props {
    order: OrderDetail
    totalWeight: number
}

defineOptions({ layout: AdminLayout })

const props = defineProps<Props>()

useHead({
    title: () => `Commande ${props.order.reference}`,
})

const { formatPrice } = useNumbers()
const { getStatus } = useStatus()
const { toParagraphs } = useStrings()

const displayEditDialog = ref(false)

const shippingAddress = computed(() => {
    if (props.order.guest) {
        return props.order.guest.shipping_address
    }
    else if (props.order.user) {
        return props.order.user.shipping_address
    }
    else {
        return undefined
    }
})

const billingAddress = computed(() => {
    if (props.order.useSameAddress) {
        return undefined
    }
    else if (props.order.guest) {
        return props.order.guest.billing_address ?? undefined
    }
    else if (props.order.user) {
        return props.order.user.billing_address ?? undefined
    }
    else {
        return undefined
    }
})

const stripeFees = computed(() => {
    const itemsPrice = props.order.details.reduce((acc, detail) => {
        acc += detail.price
        return acc
    }, 0)

    return itemsPrice * 0.015 + 0.25
})

const shipmentFees = computed(() => props.totalWeight > 400 ? 7 : 4)
</script>

<template>
    <VContainer>
        <VRow>
            <VCol>
                <h1>
                    <VIcon icon="mdi-package-variant-closed" />
                    Commande {{ order.reference }}
                    <VChip
                        v-bind="getStatus(order.status)"
                        class="ml-2"
                    />
                </h1>
            </VCol>
            <VCol class="text-end">
                <VBtn
                    variant="outlined"
                    icon="mdi-pencil"
                    @click="displayEditDialog = true"
                />
            </VCol>
        </VRow>
        <VRow>
            <VCol>
                <VCard>
                    <template #text>
                        <VAlert
                            v-if="order.guest"
                            variant="outlined"
                            color="primary"
                            icon="mdi-information"
                        >
                            <template
                                v-if="order.guest.email === 'anonyme@rgpd.fr'"
                                #text
                            >
                                Les données de l'utilisateur à l'origine de cette commande ont été anonymisées.
                            </template>
                            <template
                                v-else
                                #text
                            >
                                Cette commande a été faite par un utilisateur qui n'a pas souhaité créer de compte. <br>
                                Une anonymisation de ses données personnelles (addresse email et postale) sera effectuée
                                automatiquement si la commande est annulée ou 2 semaines après qu'elle soit terminée.
                            </template>
                        </VAlert>

                        <VContainer>
                            <VRow>
                                <VCol
                                    cols="12"
                                    md="8"
                                >
                                    <VRow no-gutters>
                                        <VCol>
                                            <h2 class="mb-4">
                                                Informations personnelles
                                            </h2>
                                            <p>
                                                <b>Adresse e-mail :</b> {{ order.user?.email }}
                                            </p>
                                            <template v-if="order.user?.instagram">
                                                <p class="font-weight-bold">
                                                    Instagram :

                                                    <VChip
                                                        :text="order.user.instagram"
                                                        :href="`https://instagram.com/${order.user.instagram}`"
                                                        target="_blank"
                                                        color="secondary"
                                                        prepend-icon="mdi-open-in-new"
                                                        class="ml-2"
                                                    />
                                                </p>
                                            </template>
                                        </VCol>
                                    </VRow>
                                    <VRow no-gutters>
                                        <VCol>
                                            <p class="font-weight-bold">
                                                Demande :
                                            </p>
                                            <div v-html="toParagraphs(order.additionalInfos)" />
                                        </VCol>
                                    </VRow>
                                    <VDivider />
                                    <VRow>
                                        <VCol
                                            v-if="shippingAddress"
                                            cols="12"
                                            md="6"
                                        >
                                            <h2 class="mb-4">
                                                Adresse de livraison
                                            </h2>
                                            <p class="mb-1">
                                                {{ shippingAddress.firstName }} {{ shippingAddress.lastName }}
                                            </p>
                                            <p class="mb-1">
                                                {{ shippingAddress.street }}
                                            </p>
                                            <p
                                                v-if="shippingAddress.street2.length > 1"
                                                class="mb-1"
                                            >
                                                {{ shippingAddress.street2 }}
                                            </p>
                                            <p class="mb-1">
                                                {{ shippingAddress.zipCode }}
                                                {{ shippingAddress.city }} -
                                                {{ shippingAddress.country }}
                                            </p>
                                        </VCol>
                                        <VCol
                                            v-if="billingAddress"
                                            cols="12"
                                            md="6"
                                        >
                                            <h2 class="mb-4">
                                                Adresse de facturation
                                            </h2>
                                            <p class="mb-1">
                                                {{ billingAddress.firstName }} {{ billingAddress.lastName }}
                                            </p>
                                            <p class="mb-1">
                                                {{ billingAddress.street }}
                                            </p>
                                            <p
                                                v-if="billingAddress.street2.length > 1"
                                                class="mb-1"
                                            >
                                                {{ billingAddress.street2 }}
                                            </p>
                                            <p class="mb-1">
                                                {{ billingAddress.zipCode }}
                                                {{ billingAddress.city }} -
                                                {{ billingAddress.country }}
                                            </p>
                                        </VCol>
                                    </VRow>
                                </VCol>
                                <VCol
                                    cols="12"
                                    md="4"
                                >
                                    <h2>
                                        Information commande
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
                                                {{ formatPrice(stripeFees) }}
                                            </template>
                                        </VListItem>
                                        <VDivider />
                                        <VListItem
                                            :title="`Frais de port (${totalWeight} g)`"
                                        >
                                            <template #append>
                                                {{ formatPrice(shipmentFees) }}
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
            </VCol>
        </VRow>
    </VContainer>
</template>
