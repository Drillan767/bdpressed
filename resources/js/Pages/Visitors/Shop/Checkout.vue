<script setup lang="ts">
import type { OrderStep1Form, OrderStep2Form, User } from '@/types'
import CartItem from '@/Components/Shop/CartItem.vue'
import OrderStep1 from '@/Components/Shop/OrderStep1.vue'
import OrderStep2 from '@/Components/Shop/OrderStep2.vue'
import useNumbers from '@/Composables/numbers'
import VisitorsLayout from '@/Layouts/VisitorsLayout.vue'
import useCartStore from '@/Stores/cartStore'
import { router } from '@inertiajs/vue3'
import useStrings from '@/Composables/strings'
import { useHead } from '@vueuse/head'
import { storeToRefs } from 'pinia'
import { computed, ref, watch } from 'vue'

defineOptions({ layout: VisitorsLayout })

const props = defineProps<{
    errors?: Record<string, string>
    auth: {
        user: User | null
    }
}>()

const { cart, tax, totalPrice, totalWeight } = storeToRefs(useCartStore())
const { formatPrice } = useNumbers()
const { handleQuantity, removeItem } = useCartStore()
const { toParagraphs } = useStrings()

const emailExists = ref(false)
const step = ref(1)
const personalInformation = ref<OrderStep1Form>({
    email: props.auth.user?.email ?? '',
    guest: props.auth.user === null,
})
const step1Valid = ref(false)
const step2Valid = ref(false)

const addresses = ref<OrderStep2Form>({
    useSameAddress: true,
    shippingAddress: {
        firstName: '',
        lastName: '',
        street: '',
        street2: '',
        city: '',
        zipCode: '',
        country: '',
    },
})

const disabled = computed(() => {
    if (step.value === 1) {
        return step1Valid.value ? 'prev' : true
    }
    else if (step.value === 2) {
        return step2Valid.value ? undefined : 'next'
    }
    else if (step.value === 3) {
        return 'next'
    }
    else {
        return undefined
    }
})

async function submit() {
    router.post(route('shop.order'), {
        user: personalInformation.value,
        products: cart.value.map(item => ({
            id: item.id,
            quantity: item.quantity,
        })),
        // TODO: add address id once available
        addresses: {
            shipping: addresses.value.shippingAddress,
            same: addresses.value.useSameAddress,
            billing: addresses.value.useSameAddress ? undefined : addresses.value.billingAddress,
        },
    })
}

watch(() => props.errors?.['user.email'], (value) => {
    if (value === 'email exists') {
        emailExists.value = true
        step.value = 1
    }
}, { immediate: true })

useHead({
    title: 'Commander',
})
</script>

<template>
    <VContainer>
        <VRow>
            <VCol>
                <VCard class="bede-block">
                    <VCardText>
                        <VContainer>
                            <VRow>
                                <VCol
                                    cols="12"
                                    md="8"
                                >
                                    <h1 class="text-secondary mb-4">
                                        Informations de commande
                                    </h1>
                                    <VAlert
                                        variant="outlined"
                                        color="secondary"
                                        icon="mdi-information-outline"
                                        text="Le paiement ne se fera que lorsque la commande sera prête à partir, vous n'avez rien à payer tout de suite ¥"
                                    />

                                    <VStepper
                                        v-model="step"
                                        :flat="true"
                                        color="primary"
                                        class="my-4"
                                    >
                                        <template #default="{ prev, next }">
                                            <VStepperHeader class="elevation-0">
                                                <VStepperItem
                                                    :complete="step > 1"
                                                    :value="1"
                                                    :subtitle="step1Valid && personalInformation.email ? personalInformation.email : undefined"
                                                    title="Informations personnelles"
                                                />

                                                <VDivider />

                                                <VStepperItem
                                                    :complete="step > 2"
                                                    :value="2"
                                                    :subtitle="step2Valid ? `${addresses.shippingAddress.city}, ${addresses.shippingAddress.country}` : undefined"
                                                    title="Livraison"
                                                />

                                                <VDivider />

                                                <VStepperItem
                                                    :complete="step === 3"
                                                    :value="3"
                                                    title="Récapitulatif"
                                                />
                                            </VStepperHeader>
                                            <VStepperWindow>
                                                <VStepperWindowItem
                                                    :value="1"
                                                    class="py-2"
                                                >
                                                    <OrderStep1
                                                        v-model:form="personalInformation"
                                                        v-model:valid="step1Valid"
                                                        :errors
                                                        :show-email-exists="emailExists"
                                                        :authenticated="auth.user !== null"
                                                    />
                                                </VStepperWindowItem>
                                                <VStepperWindowItem
                                                    :value="2"
                                                    class="py-2"
                                                >
                                                    <OrderStep2
                                                        v-model:form="addresses"
                                                        v-model:valid="step2Valid"
                                                        :authenticated="auth.user !== null"
                                                    />
                                                </VStepperWindowItem>
                                                <VStepperWindowItem
                                                    :value="3"
                                                    class="py-2"
                                                >
                                                    <VRow>
                                                        <VCol
                                                            cols="12"
                                                            md="6"
                                                        >
                                                            <p>
                                                                Création de compte :
                                                                <b>
                                                                    {{ personalInformation.guest ? 'Non' : 'Oui' }}
                                                                </b>
                                                            </p>
                                                            <p v-if="personalInformation.instagram">
                                                                Identifiant Instagram :
                                                                <b>
                                                                    {{ personalInformation.instagram }}
                                                                </b>
                                                            </p>
                                                        </VCol>
                                                        <VCol
                                                            v-if="personalInformation.additionalInfos.length > 0"
                                                            cols="12"
                                                            md="6"
                                                        >
                                                            <p class="font-weight-bold">Informartions complémentaires</p>

                                                            <div v-html="toParagraphs(personalInformation.additionalInfos)" />
                                                        </VCol>
                                                    </VRow>
                                                    <VDivider class="my-4" />
                                                    <VRow>
                                                        <VCol
                                                            cols="12"
                                                            md="3"
                                                            offset-md="3"
                                                        >
                                                            <h3 class="mb-2">
                                                                Adresse de livraison
                                                            </h3>
                                                            <p>
                                                                {{ addresses.shippingAddress.firstName }}
                                                                {{ addresses.shippingAddress.lastName }}
                                                            </p>
                                                            <p>
                                                                {{ addresses.shippingAddress.street }}
                                                            </p>
                                                            <p v-if="addresses.shippingAddress.street2">
                                                                {{ addresses.shippingAddress.street2 }}
                                                            </p>
                                                            <p>
                                                                {{ addresses.shippingAddress.city }}
                                                                {{ addresses.shippingAddress.zipCode }}
                                                                {{ addresses.shippingAddress.country }}
                                                            </p>
                                                        </VCol>
                                                        <VCol
                                                            cols="12"
                                                            md="6"
                                                        >
                                                            <h3 class="mb-2">
                                                                Adresse de facturation
                                                            </h3>
                                                            <template v-if="addresses.billingAddress">
                                                                <p>
                                                                    {{ addresses.billingAddress.firstName }}
                                                                    {{ addresses.billingAddress.lastName }}
                                                                </p>
                                                                <p>
                                                                    {{ addresses.billingAddress.street }}
                                                                </p>
                                                                <p v-if="addresses.billingAddress.street2">
                                                                    {{ addresses.billingAddress.street2 }}
                                                                </p>
                                                                <p>
                                                                    {{ addresses.billingAddress.city }}
                                                                    {{ addresses.billingAddress.zipCode }}
                                                                    {{ addresses.billingAddress.country }}
                                                                </p>
                                                            </template>
                                                        </VCol>
                                                    </VRow>
                                                    <VRow>
                                                        <VCol class="d-flex justify-space-between">
                                                            <VBtn
                                                                color="primary"
                                                                variant="text"
                                                                @click="prev"
                                                            >
                                                                Précédent
                                                            </VBtn>

                                                            <VBtn
                                                                color="secondary"
                                                                variant="flat"
                                                                @click="submit"
                                                            >
                                                                ¥ Passer commande ¥
                                                            </VBtn>
                                                        </VCol>
                                                    </VRow>
                                                </VStepperWindowItem>
                                            </VStepperWindow>
                                            <VStepperActions
                                                v-if="step < 3"
                                                :disabled
                                                @click:next="next"
                                                @click:prev="prev"
                                            />
                                        </template>
                                    </VStepper>
                                </VCol>
                                <VCol
                                    cols="12"
                                    md="4"
                                >
                                    <VList>
                                        <CartItem
                                            v-for="(item, i) in cart"
                                            :key="item.id"
                                            :item="item"
                                            @quantity="handleQuantity(i, $event)"
                                            @remove="removeItem(item)"
                                        />
                                        <VListItem
                                            title="Frais"
                                        >
                                            <template #append>
                                                <span>
                                                    {{ formatPrice(tax) }}
                                                </span>
                                            </template>
                                        </VListItem>

                                        <VListItem>
                                            <template #title>
                                                Frais de livraison (estimation)
                                                <VTooltip location="top">
                                                    <template #activator="{ props: tooltip }">
                                                        <VIcon
                                                            v-bind="tooltip"
                                                            icon="mdi-help-circle"
                                                        />
                                                    </template>
                                                    <span v-if="totalWeight > 400">
                                                        Les frais d'envois sont plus élevés du fait que votre commande dépasse 400 grammes.
                                                    </span>
                                                    <span v-else>
                                                        Frais d'envoi estimé à partir du poids de votre commande.
                                                    </span>
                                                </VTooltip>
                                            </template>
                                            <template #append>
                                                <span>
                                                    {{ formatPrice(totalWeight > 400 ? 7 : 4) }}
                                                </span>
                                            </template>
                                        </VListItem>
                                        <VDivider class="my-4" />
                                        <VListItem
                                            title="Total"
                                        >
                                            <template #append>
                                                <span>
                                                    {{ formatPrice(tax + (totalWeight > 400 ? 7 : 4) + totalPrice) }}
                                                </span>
                                            </template>
                                        </VListItem>
                                    </VList>
                                </VCol>
                            </VRow>
                        </VContainer>
                    </VCardText>
                </VCard>
            </VCol>
        </VRow>
    </VContainer>
</template>

<style scoped lang="scss">
:deep(.v-alert__content) {
    padding: 8px 0;
}

:deep(.v-stepper-item__subtitle) {
    margin-top: 10px;
}
</style>
