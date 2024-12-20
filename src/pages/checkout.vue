<script setup lang="ts">
import type { SchemaType } from '@root/amplify/data/resource'
import AddressForm from '@/components/checkout/AddressForm.vue'
import CartItem from '@/components/shop/CartItem.vue'
import useNumbers from '@/composables/numbers'
import useToast from '@/composables/toast'
import useCartStore from '@/stores/cartStore'
import { useHead } from '@vueuse/head'
import { storeToRefs } from 'pinia'
import { computed, ref, watch } from 'vue'
import { useRouter } from 'vue-router'

type Address = SchemaType<'Address'>

const router = useRouter()
const { cart, tax, totalPrice } = storeToRefs(useCartStore())
const { showError } = useToast()
const { formatPrice } = useNumbers()
const { handleQuantity, removeItem } = useCartStore()

const loading = ref(false)
const shippingAddress = ref<Address>()
const shippingAddressValid = ref(false)
const billingAddress = ref<Address>()
const billingAddressValid = ref(false)
const useSameAddress = ref(true)

const submitDisabled = computed(() => loading.value || !shippingAddressValid.value || (!useSameAddress.value && !billingAddressValid.value))

watch(cart, (value) => {
    if (value.length === 0) {
        router.push('/')
            .then(() => showError('Vous devez avoir au moins un article dans votre panier'))
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
                                    <h1 class="text-secondary">
                                        Informations de commande
                                    </h1>
                                    <VAlert
                                        variant="outlined"
                                        color="secondary"
                                        icon="mdi-information-outline"
                                        text="Le paiement ne se fera que lorsque la commande sera prête à partir, vous n'avez rien à payer tout de suite ¥"
                                    />

                                    <AddressForm
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

                                        <VListItem
                                            title="Frais de livraison (estimation)"
                                        >
                                            <template #append>
                                                <span>
                                                    {{ formatPrice(4) }}
                                                </span>
                                            </template>
                                        </VListItem>
                                        <VDivider class="my-4" />
                                        <VListItem
                                            title="Total"
                                        >
                                            <template #append>
                                                <span>
                                                    {{ formatPrice(tax + totalPrice) }}
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
