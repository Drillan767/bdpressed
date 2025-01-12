<script setup lang="ts">
import type { Address } from '@/types'
import { inject, ref } from 'vue'
import { route } from 'ziggy-js'

interface Props {
    address: Address
}

const props = defineProps<Props>()

const emit = defineEmits<{
    (e: 'success'): void
}>()

const displayDialog = defineModel<boolean>({ required: true })

const csrfToken = inject<string>('csrfToken')

const loading = ref(false)

async function submit() {
    if (!csrfToken)
        return
    loading.value = true

    await fetch(route('user.addresses.destroy', { address: props.address.id }), {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
        },
    })

    loading.value = false
    displayDialog.value = false
    emit('success')
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
            title="Supprimer cette adresse ?"
            text="La suppression de cette adresse est irréversible. Continuer ?"
        >
            <template #actions>
                <VBtn
                    variant="text"
                    @click="displayDialog = false"
                >
                    Annuler
                </VBtn>
                <VBtn
                    variant="flat"
                    color="error"
                    :loading
                    :disabled="loading"
                    @click="submit"
                >
                    Supprimer
                </VBtn>
            </template>
        </VCard>
    </VDialog>
</template>
