<script setup lang="ts">
import type { Address, AddressFields, OrderStep2Form } from '@/types'
import { ref, watch } from 'vue'
import AddressForm from './AddressForm.vue'

interface Props {
    authenticated: boolean
    userAddresses: Address[]
}

const props = defineProps<Props>()

const step2 = defineModel<OrderStep2Form>('form', { required: true })
const step2Valid = defineModel<boolean>('valid', { required: true })
const shippingId = defineModel<number>('shippingId')
const billingId = defineModel<number>('billingId')

const shippingAddress = ref<AddressFields>()
const shippingAddressValid = ref(false)
const billingAddress = ref<AddressFields>()
const billingAddressValid = ref(false)
const useSameAddress = ref(true)

watch([
    shippingAddressValid,
    billingAddressValid,
    shippingId,
    billingId,
], ([
    shipping,
    billing,
    shippingId,
    billingId,
]) => {
    if (props.userAddresses.length > 0) {
        step2Valid.value = !!shippingId && !!billingId
    }
    else {
        step2Valid.value = useSameAddress.value ? shipping : shipping && billing
    }
}, { immediate: true })

watch([shippingAddress, useSameAddress, billingAddress], ([shipping, same, billing]) => {
    if (!shipping)
        return
    step2.value = {
        shippingAddress: shipping,
        billingAddress: same ? shipping : billing,
        useSameAddress: same,
    }
}, { immediate: true })
</script>

<template>
    <template v-if="authenticated && userAddresses.length > 0">
        <h2 class="mb-4">
            Adresse de livraison
        </h2>
        <VRow>
            <VCol
                v-for="(address, i) in userAddresses"
                :key="i"
                cols="12"
                md="4"
            >
                <VItemGroup
                    v-model="shippingId"
                >
                    <VItem
                        v-slot="{ isSelected, toggle }"
                        :value="address.id"
                    >
                        <VCard
                            :title="`Adresse ${i + 1}${address.default_shipping ? ' (défaut)' : ''}`"
                            :variant="isSelected ? 'tonal' : 'outlined'"
                            color="primary"
                            @click="toggle"
                        >
                            <template #text>
                                <p class="mb-0">
                                    {{ address.firstName }} {{ address.lastName }}
                                </p>
                                <p class="mb-0">
                                    {{ address.street }}
                                </p>
                                <p v-if="address.street2">
                                    {{ address.street2 }}
                                </p>
                                <p class="mb-0">
                                    {{ address.zipCode }} {{ address.city }} -
                                    {{ address.country }}
                                </p>
                            </template>
                        </VCard>
                    </VItem>
                </VItemGroup>
            </VCol>
        </VRow>
        <h2 class="my-4">
            Adresse de facturation
        </h2>
        <VRow>
            <VCol
                v-for="(address, i) in userAddresses"
                :key="i"
                cols="12"
                md="4"
            >
                <VItemGroup
                    v-model="billingId"
                >
                    <VItem
                        v-slot="{ isSelected, toggle }"
                        :value="address.id"
                    >
                        <VCard
                            :title="`Adresse ${i + 1}${address.default_billing ? ' (défaut)' : ''}`"
                            :variant="isSelected ? 'tonal' : 'outlined'"
                            color="secondary"
                            @click="toggle"
                        >
                            <template #text>
                                <p class="mb-0">
                                    {{ address.firstName }} {{ address.lastName }}
                                </p>
                                <p class="mb-0">
                                    {{ address.street }}
                                </p>
                                <p v-if="address.street2">
                                    {{ address.street2 }}
                                </p>
                                <p class="mb-0">
                                    {{ address.zipCode }} {{ address.city }} -
                                    {{ address.country }}
                                </p>
                            </template>
                        </VCard>
                    </VItem>
                </VItemGroup>
            </VCol>
        </VRow>
    </template>
    <template v-else>
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
                        label="L'adresse de facturation est la même que celle de livraison ?"
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
    </template>
</template>
