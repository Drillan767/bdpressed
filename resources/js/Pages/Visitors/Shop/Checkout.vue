<script setup lang="ts">
import type { Address, OrderStep1Form, User } from '@/types'
import CartItem from '@/Components/Shop/CartItem.vue'
import OrderStep1 from '@/Components/Shop/OrderStep1.vue'
import OrderStep2 from '@/Components/Shop/OrderStep2.vue'
import useNumbers from '@/Composables/numbers'
import useToast from '@/Composables/toast'
import VisitorsLayout from '@/Layouts/VisitorsLayout.vue'
import useCartStore from '@/Stores/cartStore'
import { router } from '@inertiajs/vue3'
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
const { showError } = useToast()
const { formatPrice } = useNumbers()
const { handleQuantity, removeItem } = useCartStore()

const loading = ref(false)
const step = ref(1)
const personalInformation = ref<OrderStep1Form>({
    email: props.auth.user?.email ?? '',
    guest: props.auth.user === null,
})
const step1Valid = ref(false)
const shippingAddressValid = ref(false)
const billingAddressValid = ref(false)
const useSameAddress = ref(true)

const submitDisabled = computed(() => loading.value || !shippingAddressValid.value || (!useSameAddress.value && !billingAddressValid.value))

const disabled = computed(() => {
    if (step.value === 1) {
        return step1Valid.value ? 'prev' : true
    }
    else if (step.value === 3) {
        return 'next'
    }
    else {
        return undefined
    }
})

watch(cart, (value) => {
    if (value.length === 0) {
        showError('Vous devez avoir au moins un article dans votre panier')
        router.visit('/')
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
                                                    title="Informations personnelles"
                                                    editable
                                                />

                                                <VDivider />

                                                <VStepperItem
                                                    :complete="step > 2"
                                                    :value="2"
                                                    title="Livraison"
                                                    editable
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
                                                        :authenticated="auth.user !== null"
                                                    />
                                                </VStepperWindowItem>
                                                <VStepperWindowItem
                                                    :value="2"
                                                    class="py-2"
                                                >
                                                    <OrderStep2
                                                        :authenticated="auth.user !== null"
                                                    />
                                                </VStepperWindowItem>
                                                <VStepperWindowItem
                                                    :value="3"
                                                    class="py-2"
                                                >
                                                    Étape 3
                                                </VStepperWindowItem>
                                            </VStepperWindow>
                                            <VStepperActions
                                                :disabled
                                                @click:next="next"
                                                @click:prev="prev"
                                            />
                                        </template>
                                    </VStepper>

                                    <VCardActions>
                                        <VBtn
                                            variant="flat"
                                            color="secondary"
                                            :disabled="submitDisabled"
                                            block
                                        >
                                            ¥ Passer commande ¥
                                        </VBtn>
                                    </VCardActions>
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
</style>
