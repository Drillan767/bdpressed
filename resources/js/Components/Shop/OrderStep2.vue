<script setup lang="ts">
import type { Address, OrderStep2Form } from '@/types'
import AddressForm from '@/Components/Shop/AddressForm.vue'
import { ref, watch } from 'vue'

interface Props {
    authenticated: boolean
}

defineProps<Props>()

const step2 = defineModel<OrderStep2Form>('form', { required: true })
const step2Valid = defineModel<boolean>('valid', { required: true })

const shippingAddress = ref<Address>()
const shippingAddressValid = ref(false)
const billingAddress = ref<Address>()
const billingAddressValid = ref(false)
const useSameAddress = ref(true)

watch([shippingAddressValid, billingAddressValid], ([shipping, billing]) => {
    step2Valid.value = useSameAddress.value ? shipping : shipping && billing
})

watch([shippingAddress, useSameAddress, billingAddress], ([shipping, same, billing]) => {
    if (!shipping)
        return
    step2.value = {
        shippingAddress: shipping,
        billingAddress: same ? shipping : billing,
        useSameAddress: same,
    }
})
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
