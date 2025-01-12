<script setup lang="ts">
import type { Address, AddressFields } from '@/types'
import AddressFormComponent from '@/Components/Shop/AddressForm.vue'
import { inject, ref, watch } from 'vue'
import { route } from 'ziggy-js'

interface Props {
    address: Address
}

const props = defineProps<Props>()

const emit = defineEmits<{
    (e: 'success'): void
}>()

const csrfToken = inject<string>('csrfToken')

const displayDialog = defineModel<boolean>({ required: true })

const form = ref<AddressFields>({
    firstName: '',
    lastName: '',
    street: '',
    street2: '',
    city: '',
    zipCode: '',
    country: '',
})

const formValid = ref(false)
const loading = ref(false)

async function submit() {
    if (!csrfToken)
        return
    loading.value = true

    await fetch(route('user.addresses.update', { address: props.address.id }), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            ...form.value,
            _method: 'PUT',
        }),
    })

    loading.value = false
    displayDialog.value = false
    emit('success')
}

watch(() => props.address, (address) => {
    if (address) {
        form.value = {
            firstName: address.firstName,
            lastName: address.lastName,
            street: address.street,
            street2: address.street2,
            city: address.city,
            zipCode: address.zipCode,
            country: address.country,
        }
    }
}, { immediate: true })
</script>

<template>
    <VDialog
        v-model="displayDialog"
        max-width="960"
    >
        <VCard
            :loading="loading ? 'primary' : false"
            prepend-icon="mdi-package-variant-plus"
            title="Créer une adresse"
        >
            <VContainer>
                <AddressFormComponent
                    v-model:address="form"
                    v-model:valid="formValid"
                    :edit="false"
                />
            </VContainer>
            <template #actions>
                <VBtn
                    variant="text"
                    @click="displayDialog = false"
                >
                    Annuler
                </VBtn>
                <VBtn
                    variant="flat"
                    color="primary"
                    :loading
                    :disabled="!formValid || loading"
                    @click="submit"
                >
                    Enregistrer
                </VBtn>
            </template>
        </VCard>
    </VDialog>
</template>
