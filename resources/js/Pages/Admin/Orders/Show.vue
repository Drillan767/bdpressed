<script setup lang="ts">
import type { OrderDetail } from '@/types'
import useNumbers from '@/Composables/numbers'
import useStatus from '@/Composables/status'
import useStrings from '@/Composables/strings'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { router } from '@inertiajs/vue3'
import { useHead } from '@vueuse/head'
import { route } from 'ziggy-js'

interface Props {
    order: OrderDetail
    totalWeight: number
}

defineOptions({ layout: AdminLayout })

const props = defineProps<Props>()

useHead({
    title: () => `Commande ${props.order.reference}`,
})

function updateStatus() {
    console.log('updateStatus')
}

const { formatPrice } = useNumbers()
const { orderStatus, getOrderStatus, getIllustrationStatus } = useStatus()
const { toParagraphs } = useStrings()
</script>

<template>
    <VContainer>
        <VRow>
            <VCol
                cols="12"
                md="8"
            >
                <VRow>
                    <VCol class="d-flex align-center flex-shrink-1 flex-grow-0">
                        <VBtn
                            href="/admin/commandes"
                            prepend-icon="mdi-arrow-left"
                        >
                            Retour
                        </VBtn>
                    </VCol>
                    <VCol>
                        <h1>
                            Commande {{ order.reference }}
                        </h1>
                        <p>
                            Placée le {{ order.created_at }}
                        </p>
                    </VCol>
                </VRow>
                <VRow>
                    <VCol>
                        <VCard
                            title="Statut de la commande"
                        >
                            <template #append>
                                <VChip v-bind="getOrderStatus(order.status)" />
                            </template>
                            <template #text>
                                <VSelect
                                    :items="orderStatus"
                                    item-title="text"
                                    item-value="internal"
                                    :model-value="order.status"
                                    variant="outlined"
                                    density="compact"
                                    hide-details
                                    label="Changer le statut"
                                />
                            </template>
                            <template #actions>
                                <VSpacer />
                                <VBtn
                                    color="primary"
                                    variant="flat"
                                    @click="updateStatus"
                                >
                                    Enregistrer
                                </VBtn>
                            </template>
                        </VCard>
                    </VCol>
                </VRow>
                <VRow v-if="order.guest_id">
                    <VCol>
                        <VAlert
                            variant="outlined"
                            color="primary"
                            icon="mdi-information"
                        >
                            <template
                                v-if="order.client.email === 'anonyme@rgpd.fr'"
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
                    </VCol>
                </VRow>
                <VRow>
                    <VCol>
                        <VCard
                            title="Articles"
                        >
                            <template #text>
                                <VCard
                                    v-for="(detail, i) in order.details"
                                    :key="i"
                                    :title="detail.product.name"
                                    :subtitle="`${detail.quantity} x ${formatPrice(detail.price)}`"
                                    variant="tonal"
                                    class="mb-4"
                                >
                                    <template #prepend>
                                        <VImg
                                            :src="detail.product.promotedImage"
                                            :alt="detail.product.name"
                                            width="100"
                                            height="100"
                                            rounded="lg"
                                            cover
                                        />
                                    </template>
                                    <template #append>
                                        {{ formatPrice(detail.price * detail.quantity) }}
                                    </template>
                                </VCard>
                                <VCard
                                    v-for="(illustration, i) in order.illustrations"
                                    :key="i"
                                    :title="`Illustration n°${i + 1}`"
                                    variant="tonal"
                                    class="mb-4"
                                >
                                    <template #prepend>
                                        <VImg
                                            src="/assets/images/yell.png"
                                            width="100"
                                            height="100"
                                            rounded="lg"
                                            cover
                                        />
                                    </template>

                                    <template #subtitle>
                                        <VChip
                                            v-bind="getIllustrationStatus(illustration.status)"
                                            class="mr-2"
                                        />
                                    </template>
                                    <template #append>
                                        <div class="d-flex flex-column ga-2">
                                            <span class="text-end">
                                                {{ formatPrice(illustration.price) }}
                                            </span>
                                            <VBtn
                                                :href="route('admin.illustrations.show', { illustration: illustration.id })"
                                                append-icon="mdi-magnify"
                                                variant="text"
                                                size="small"
                                            >
                                                Détails
                                            </VBtn>
                                        </div>
                                    </template>
                                </VCard>
                                <VDivider class="my-4" />
                                <VList>
                                    <VListItem title="Sous-total">
                                        <template #append>
                                            {{ formatPrice(order.total) }}
                                        </template>
                                    </VListItem>
                                    <VListItem title="Frais de port + paiement">
                                        <template #append>
                                            {{ formatPrice(order.shipmentFees + order.stripeFees) }}
                                        </template>
                                    </VListItem>
                                    <VListItem>
                                        <template #title>
                                            <b>Total</b>
                                        </template>
                                        <template #append>
                                            <b class="text-primary">
                                                {{ formatPrice(order.total) }}
                                            </b>
                                        </template>
                                    </VListItem>
                                </VList>
                            </template>
                        </VCard>
                    </VCol>
                </VRow>
            </VCol>
            <VCol
                cols="12"
                md="4"
            />
        </VRow>

        <!--
        <VRow>
            <VCol>
                <h1>
                    <VIcon icon="mdi-package-variant-closed" />
                    Commande {{ order.reference }}
                    <VChip
                        v-bind="getOrderStatus(order.status)"
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
                            v-if="order.guest_id"
                            variant="outlined"
                            color="primary"
                            icon="mdi-information"
                        >
                            <template
                                v-if="order.client.email === 'anonyme@rgpd.fr'"
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
                                                <b class="mr-2">Adresse e-mail :</b>
                                                {{ order.client.email }}
                                            </p>
                                            <template v-if="order.client?.instagram">
                                                <p class="font-weight-bold">
                                                    Instagram :

                                                    <VChip
                                                        :text="order.client.instagram"
                                                        :href="`https://instagram.com/${order.client.instagram}`"
                                                        target="_blank"
                                                        color="secondary"
                                                        prepend-icon="mdi-open-in-new"
                                                        class="ml-2"
                                                    />
                                                </p>
                                            </template>
                                        </VCol>
                                    </VRow>
                                    <VRow
                                        v-if="order.additionalInfos"
                                        no-gutters
                                    >
                                        <VCol>
                                            <p class="font-weight-bold">
                                                Demande :
                                            </p>
                                            <div v-html="toParagraphs(order.additionalInfos)" />
                                        </VCol>
                                    </VRow>
                                    <VRow>
                                        <VCol
                                            v-if="order.client.shipping_address"
                                            cols="12"
                                            md="6"
                                        >
                                            <h2 class="mb-4">
                                                Adresse de livraison
                                            </h2>
                                            <p class="mb-1">
                                                {{ order.client.shipping_address.firstName }} {{ order.client.shipping_address.lastName }}
                                            </p>
                                            <p class="mb-1">
                                                {{ order.client.shipping_address.street }}
                                            </p>
                                            <p
                                                v-if="order.client.shipping_address.street2"
                                                class="mb-1"
                                            >
                                                {{ order.client.shipping_address.street2 }}
                                            </p>
                                            <p class="mb-1">
                                                {{ order.client.shipping_address.zipCode }}
                                                {{ order.client.shipping_address.city }} -
                                                {{ order.client.shipping_address.country }}
                                            </p>
                                        </VCol>
                                        <VCol
                                            v-if="order.client.billing_address"
                                            cols="12"
                                            md="6"
                                        >
                                            <h2 class="mb-4">
                                                Adresse de facturation
                                            </h2>
                                            <p class="mb-1">
                                                {{ order.client.billing_address.firstName }} {{ order.client.billing_address.lastName }}
                                            </p>
                                            <p class="mb-1">
                                                {{ order.client.billing_address.street }}
                                            </p>
                                            <p
                                                v-if="order.client.billing_address.street2"
                                                class="mb-1"
                                            >
                                                {{ order.client.billing_address.street2 }}
                                            </p>
                                            <p class="mb-1">
                                                {{ order.client.billing_address.zipCode }}
                                                {{ order.client.billing_address.city }} -
                                                {{ order.client.billing_address.country }}
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
                                        <template v-if="order.illustrationsList.length > 0">
                                            <VDivider />

                                            <VListGroup
                                                v-for="(illustration, i) in order.illustrationsList"
                                                :key="i"
                                            >
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
                                        <VDivider />
                                        <VListItem
                                            :title="`Frais de port (${totalWeight} g)`"
                                        >
                                            <template #append>
                                                {{ formatPrice(order.shipmentFees) }}
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
        -->
    </VContainer>
</template>
