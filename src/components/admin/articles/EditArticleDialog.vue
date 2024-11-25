<script setup lang="ts">
import type { ProductForm } from '@/types'
import type { SchemaType } from '@root/amplify/data/resource'
import useBuckets from '@/composables/buckets'
import useProductsStore from '@/stores/productsStore'
import { ref, watch } from 'vue'
import ArticleForm from './ArticleForm.vue'

type Product = SchemaType<'Product'> & { id: string }

const emit = defineEmits<{
    (e: 'success'): void
}>()

const displayDialog = defineModel<boolean>({ required: true })
const editedProduct = defineModel<Product>('product', { required: true })

const { updateProduct } = useProductsStore()
const { getSingleItem } = useBuckets()

const articleForm = ref<InstanceType<typeof ArticleForm>>()
const loading = ref(false)
const displayPreview = ref(false)
const previewUrl = ref('')
const formValid = ref(false)
const form = ref<Required<ProductForm>>({
    name: '',
    quickDescription: '',
    description: '',
    price: 0,
    promotedImage: null,
    images: [],
})

async function submit() {
    loading.value = true
    await updateProduct({
        id: editedProduct.value.id,
        ...form.value,
    })
    emit('success')
    loading.value = false

    closeDialog()
}

function closeDialog() {
    displayDialog.value = false
    articleForm.value?.resetForm()
}

watch(displayDialog, async (value) => {
    if (value) {
        form.value = {
            name: editedProduct.value.name,
            quickDescription: editedProduct.value.quickDescription,
            description: editedProduct.value.description,
            price: editedProduct.value.price,
            promotedImage: null,
            images: [],
        }

        previewUrl.value = await getSingleItem(editedProduct.value.promotedImage)
    }
})
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
            :title="`Éditer ${editedProduct.name}`"
            prepend-icon="mdi-package-variant"
        >
            <VContainer>
                <ArticleForm
                    ref="articleForm"
                    v-model:form="form"
                    v-model:form-valid="formValid"
                    v-model:preview-url="previewUrl"
                    :edit="true"
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
                    Enregistrer
                </VBtn>
            </template>
        </VCard>
        <VDialog
            v-model="displayPreview"
            :width="800"
        >
            <VImg :src="previewUrl" />
        </VDialog>
    </VDialog>
</template>
