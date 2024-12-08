<script setup lang="ts">
import type { SchemaType } from '@root/amplify/data/resource'
import useProductsStore from '@/stores/productsStore'
import { storeToRefs } from 'pinia'
import { computed } from 'vue'

type Product = SchemaType<'Product'>

interface Props {
    product: Product
    modelValue: boolean
}

const props = defineProps<Props>()
const emit = defineEmits<{
    (e: 'update:modelValue', value: boolean): void
    (e: 'success'): void
}>()

const { productsLoading } = storeToRefs(useProductsStore())
const { deleteProduct } = useProductsStore()

const valueData = computed({
    get: () => props.modelValue,
    set: value => emit('update:modelValue', value),
})

function handleDelete() {
    deleteProduct(props.product)
    valueData.value = false
    emit('success')
}
</script>

<template>
    <VDialog
        v-model="valueData"
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
                    @click="valueData = false"
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
