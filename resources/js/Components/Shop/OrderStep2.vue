<script setup lang="ts">
import type { Address } from '@/types'
import AddressForm from '@/Components/Shop/AddressForm.vue'
import { ref } from 'vue'

const shippingAddress = ref<Address>()
const shippingAddressValid = ref(false)
const billingAddress = ref<Address>()
const billingAddressValid = ref(false)
const useSameAddress = ref(true)
</script>

<template>
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
