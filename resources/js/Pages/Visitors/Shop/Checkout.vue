<script setup lang="ts">
import type { Address } from '@/types'
import AddressForm from '@/Components/Shop/AddressForm.vue'
import CartItem from '@/Components/Shop/CartItem.vue'
import useNumbers from '@/Composables/numbers'
import useToast from '@/Composables/toast'
import VisitorsLayout from '@/Layouts/VisitorsLayout.vue'
import useCartStore from '@/Stores/cartStore'
import { router } from '@inertiajs/vue3'
import { useHead } from '@vueuse/head'
import { storeToRefs } from 'pinia'
import { computed, ref, watch } from 'vue'

defineOptions({ layout: VisitorsLayout })

defineProps<{
    errors?: Record<string, string>
}>()

const { cart, tax, totalPrice, totalWeight } = storeToRefs(useCartStore())
const { showError } = useToast()
const { formatPrice } = useNumbers()
const { handleQuantity, removeItem } = useCartStore()

const loading = ref(false)
const step = ref(1)
const shippingAddress = ref<Address>()
const shippingAddressValid = ref(false)
const billingAddress = ref<Address>()
const billingAddressValid = ref(false)
const useSameAddress = ref(true)
const phone = ref('')

const submitDisabled = computed(() => loading.value || !shippingAddressValid.value || (!useSameAddress.value && !billingAddressValid.value))

const disabled = computed(() => {
    if (step.value === 1) {
        return 'prev'
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
                                    md="6"
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
                                                    Étape 1
                                                </VStepperWindowItem>
                                                <VStepperWindowItem
                                                    :value="2"
                                                    class="py-2"
                                                >
                                                    Étape 2
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

                                    <!--  <AddressForm
                                        v-model:address="shippingAddress"
                                        v-model:valid="shippingAddressValid"
                                        title="Adresse de livraison"
                                    />

                                    <VContainer>
                                        <VRow>
                                            <VCol>
                                                <VSwitch
                                                    v-model="useSameAddress"
                                                    label="Utiliser la même adresse"
                                                />
                                            </VCol>
                                        </VRow>
                                    </VContainer>

                                    <AddressForm
                                        v-if="!useSameAddress"
                                        v-model:address="billingAddress"
                                        v-model:valid="billingAddressValid"
                                        title="Adresse de facturation"
                                    />

                                    <VDivider class="my-4" />

                                    <VRow no-gutters>
                                        <VCol>
                                            <VTextField
                                                v-model="phone"
                                                prepend-inner-icon="mdi-phone-outline"
                                                label="Téléphone"
                                            />
                                        </VCol>
                                    </VRow>
 -->
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
                                    md="6"
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

</style>
