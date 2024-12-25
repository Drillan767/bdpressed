<script setup lang="ts">
import type { AdminProductList } from '@/types'
import { inject, ref } from 'vue'

interface Props {
    product: AdminProductList
}

const props = defineProps<Props>()

const emit = defineEmits<{
    (e: 'update:modelValue', value: boolean): void
    (e: 'success'): void
}>()

const dialog = defineModel<boolean>({ required: true })

const csrfToken = inject<string>('csrfToken')

const productsLoading = ref(false)

async function handleDelete() {
    if (!csrfToken)
        return

    const formData = new FormData()

    formData.append('_method', 'DELETE')
    formData.append('_token', csrfToken)
    formData.append('product', String(props.product.id))

    await fetch(route('products.destroy', { product: props.product.id }), {
        method: 'POST',
        body: formData,
    })
    // deleteProduct(props.product)
    dialog.value = false
    emit('success')
}
</script>

<template>
    <VDialog
        v-model="dialog"
        max-width="500"
    >
        <VCard
            :loading="productsLoading ? 'primary' : false"
            prepend-icon="mdi-package-variant-remove"
            title="Supprimer l'article ?"
            text="Êtes-vous sûr de vouloir supprimer cet article ?"
        >
            <template #actions>
                <VBtn
                    variant="text"
                    @click="dialog = false"
                >
                    Annuler
                </VBtn>
                <VBtn
                    :loading="productsLoading"
                    :disabled="productsLoading"
                    color="error"
                    variant="text"
                    @click="handleDelete"
                >
                    Supprimer
                </VBtn>
            </template>
        </VCard>
    </VDialog>
</template>
