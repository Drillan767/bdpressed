<script setup lang="ts">
import type { AddressFields } from '@/types'
import AddressFormComponent from '@/Components/Shop/AddressForm.vue'
import { inject, ref } from 'vue'
import { route } from 'ziggy-js'

const emit = defineEmits<{
    (e: 'success'): void
}>()

const csrfToken = inject<string>('csrfToken')

const displayDialog = defineModel<boolean>({ required: true })

const loading = ref(false)
const formValid = ref(false)
const addressForm = ref<InstanceType<typeof AddressFormComponent>>()
const form = ref<AddressFields>({
    firstName: '',
    lastName: '',
    street: '',
    street2: '',
    city: '',
    zipCode: '',
    country: '',
})

async function submit() {
    if (!formValid.value || !csrfToken)
        return

    loading.value = true

    await fetch(route('user.addresses.store'), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(form.value),
    })

    loading.value = false
    displayDialog.value = false
    emit('success')
    addressForm.value?.resetForm()
}

function close() {
    displayDialog.value = false
    addressForm.value?.resetForm()
}
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
                    ref="addressForm"
                    v-model:address="form"
                    v-model:valid="formValid"
                    :edit="false"
                />
            </VContainer>
            <template #actions>
                <VBtn
                    variant="text"
                    @click="close"
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
                    Créer
                </VBtn>
            </template>
        </VCard>
    </VDialog>
</template>
