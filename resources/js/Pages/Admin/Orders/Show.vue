<script setup lang="ts">
import type { TransitionWarning } from '@/Composables/warnings'
import type { OrderDetail, PaymentHistory as Payment } from '@/types'
import type { OrderStatus } from '@/types/enums'
import StatusChangeHistory from '@/Components/Admin/StatusChangeHistory.vue'
import PaymentTimeline from '@/Components/Order/PaymentTimeline.vue'
import StatusChangeWarning from '@/Components/Order/StatusChangeWarning.vue'
import useNumbers from '@/Composables/numbers'
import useStatus from '@/Composables/status'
import useStrings from '@/Composables/strings'
import useWarnings from '@/Composables/warnings'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { router } from '@inertiajs/vue3'
import { useHead } from '@vueuse/head'
import { computed, ref } from 'vue'
import { route } from 'ziggy-js'

interface Props {
    order: OrderDetail
    estimatedFees: number
    allowedStatuses: OrderStatus[]
    isIllustrationOnly: boolean
    paymentHistory: Payment[]
}

defineOptions({ layout: AdminLayout })

const props = defineProps<Props>()

useHead({
    title: () => `Commande ${props.order.reference}`,
})

const { formatPrice } = useNumbers()
const { getOrderStatus, getIllustrationStatus, listAvailableStatuses, getTrigger } = useStatus()
const { toParagraphs } = useStrings()
const { orderChangeWarning } = useWarnings()

const loading = ref(false)
const status = ref<OrderStatus>()
const warning = ref<TransitionWarning<OrderStatus>>()
const scw = ref<InstanceType<typeof StatusChangeWarning>>()

const statusList = computed(() => listAvailableStatuses(props.allowedStatuses, props.order.status))

const statusChangesDisplay = computed(() => {
    return props.order.status_changes.map(change => ({
        id: change.id,
        created_at: change.created_at,
        reason: change.reason,
        triggered_by: getTrigger(change.triggered_by),
        from_status: change.from_status ? getOrderStatus(change.from_status) : undefined,
        to_status: getOrderStatus(change.to_status),
    }))
})

// lmao.
function triggerWarning() {
    if (!status.value)
        return

    warning.value = orderChangeWarning(props.order.status, status.value)

    if (!warning.value)
        updateStatus()
}

function handleCancel() {
    warning.value = undefined
    status.value = undefined
}

async function updateStatus() {
    loading.value = true

    router.post(route('orders.update-status', { reference: props.order.reference }), {
        status: status.value,
        payload: scw.value?.payload,
    })

    status.value = undefined
    loading.value = false
}
</script>

<template>
    <VContainer>
        <VRow>
            <VCol class="d-flex align-center flex-shrink-1 flex-grow-0">
                <VBtn
                    href="/administration/commandes"
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
            <VCol
                cols="12"
                md="8"
            >
                <VRow v-if="isIllustrationOnly">
                    <VCol>
                        <VAlert
                            variant="outlined"
                            color="info"
                            icon="mdi-information"
                        >
                            <template #text>
                                Cette commande ne contient que des illustrations personnalisées.
                                Le statut sera automatiquement mis à jour lorsque toutes les illustrations seront terminées et payées.
                            </template>
                        </VAlert>
                    </VCol>
                </VRow>
                <VRow>
                    <VCol>
                        <VCard
                            :loading="loading"
                            :disabled="isIllustrationOnly"
                            title="Statut de la commande"
                        >
                            <template #append>
                                <VChip v-bind="getOrderStatus(order.status)" />
                            </template>
                            <template #text>
                                <VSelect
                                    v-model="status"
                                    :items="statusList"
                                    item-title="text"
                                    item-value="internal"
                                    variant="outlined"
                                    density="compact"
                                    hide-details
                                    label="Changer le statut"
                                    clearable
                                />
                            </template>
                            <template #actions>
                                <VSpacer />
                                <VBtn
                                    :disabled="!status"
                                    color="primary"
                                    variant="flat"
                                    @click="triggerWarning"
                                >
                                    Enregistrer
                                </VBtn>
                            </template>
                        </VCard>
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
                                    :subtitle="`${detail.quantity} x ${detail.product.price.formatted}`"
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
                                        {{ detail.price.formatted }}
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
                                                {{ illustration.price.formatted }}
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
                                            {{ order.total.formatted }}
                                        </template>
                                    </VListItem>
                                    <VListItem title="Frais de port + paiement">
                                        <template #append>
                                            {{ formatPrice(estimatedFees / 100) }}
                                        </template>
                                    </VListItem>
                                    <VListItem>
                                        <template #title>
                                            <b>Total</b>
                                        </template>
                                        <template #append>
                                            <b class="text-primary">
                                                {{ formatPrice(order.total.euros + estimatedFees / 100) }}
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
                            title="Informations client"
                        >
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
                                <h4 class="my-4">
                                    Détails
                                </h4>
                                <VCard variant="tonal">
                                    <template #text>
                                        <p>
                                            <b>Adresse e-mail :</b>
                                            {{ order.client.email }}
                                        </p>
                                        <p v-if="order.client.instagram">
                                            <b>Instagram :</b>
                                            {{ order.client.instagram }}
                                        </p>
                                    </template>
                                </VCard>
                                <template v-if="order.additionalInfos">
                                    <h4 class="my-4">
                                        Informations additionnelles
                                    </h4>
                                    <VCard variant="tonal">
                                        <template #text>
                                            <div v-html="toParagraphs(order.additionalInfos)" />
                                        </template>
                                    </VCard>
                                </template>
                            </template>
                        </VCard>
                    </VCol>
                </VRow>
            </VCol>
            <VCol
                cols="12"
                md="4"
            >
                <VRow>
                    <VCol>
                        <VCard
                            title="Adresse de livraison"
                        >
                            <template #text>
                                <p>
                                    {{ order.client.shipping_address.firstName }} {{ order.client.shipping_address.lastName }}
                                </p>
                                <p>
                                    {{ order.client.shipping_address.street }}
                                </p>
                                <p>
                                    {{ order.client.shipping_address.street2 }}
                                </p>
                                <p>
                                    {{ order.client.shipping_address.zipCode }}
                                    {{ order.client.shipping_address.city }} -
                                    {{ order.client.shipping_address.country }}
                                </p>
                            </template>
                        </VCard>
                    </VCol>
                </VRow>
                <VRow v-if="order.client.billing_address">
                    <VCol>
                        <VCard title="Adresse de facturation">
                            <template #text>
                                <p v-if="order.client.billing_address">
                                    {{ order.client.billing_address.firstName }} {{ order.client.billing_address.lastName }}
                                </p>
                                <p v-if="order.client.billing_address.street">
                                    {{ order.client.billing_address.street }}
                                </p>
                                <p v-if="order.client.billing_address.street2">
                                    {{ order.client.billing_address.street2 }}
                                </p>
                                <p v-if="order.client.billing_address.zipCode">
                                    {{ order.client.billing_address.zipCode }}
                                    {{ order.client.billing_address.city }} -
                                    {{ order.client.billing_address.country }}
                                </p>
                            </template>
                        </VCard>
                    </VCol>
                </VRow>
                <VRow>
                    <VCol>
                        <PaymentTimeline :payments="paymentHistory" />
                    </VCol>
                </VRow>
                <VRow>
                    <VCol>
                        <StatusChangeHistory :status-changes="statusChangesDisplay" />
                    </VCol>
                </VRow>
            </VCol>
        </VRow>
        <StatusChangeWarning
            ref="scw"
            :warning
            @submit="updateStatus"
            @cancel="handleCancel"
        />
    </VContainer>
</template>
