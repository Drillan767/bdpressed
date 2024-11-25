<script setup lang="ts">
import type { ProductForm } from '@/types'
import useProductsStore from '@/stores/productsStore'
import { ref } from 'vue'
import ArticleForm from './ArticleForm.vue'

const emit = defineEmits<{
    (e: 'success'): void
}>()

const form = ref<Required<ProductForm>>({
    name: '',
    quickDescription: '',
    description: '',
    price: 0,
    promotedImage: null,
    images: [],
})

const displayDialog = defineModel<boolean>({ required: true })

const { storeProducts } = useProductsStore()

const formValid = ref(false)
const loading = ref(false)
const articleForm = ref<InstanceType<typeof ArticleForm>>()

async function submit() {
    loading.value = true
    await storeProducts(form.value)
    loading.value = false

    closeDialog()
}

function closeDialog() {
    displayDialog.value = false
    articleForm.value?.resetForm()
    emit('success')
}
</script>

<template>
    <VDialog
        v-model="displayDialog"
        max-width="960"
        persistent
        scrollable
    >
        <VCard
            :loading
            prepend-icon="mdi-package-variant-plus"
            title="Créer un article"
        >
            <VContainer>
                <ArticleForm
                    ref="articleForm"
                    v-model:form="form"
                    v-model:form-valid="formValid"
                    :edit="false"
                />
            </VContainer>

            <template #actions>
                <VBtn
                    variant="text"
                    @click="closeDialog"
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
