<script setup lang="ts">
import type { AdminProduct, ProductForm } from '@/types'
import { inject, ref, watch } from 'vue'
import ArticleForm from './ArticleForm.vue'

interface Props {
    product: AdminProduct
}

const props = defineProps<Props>()

const emit = defineEmits<{
    (e: 'success'): void
}>()

const displayDialog = defineModel<boolean>({ required: true })
const editedProduct = defineModel<AdminProduct>('product', { required: true })

const csrfToken = inject<string>('csrfToken')

const articleForm = ref<InstanceType<typeof ArticleForm>>()
const loading = ref(false)
const displayPreview = ref(false)
const previewUrl = ref('')
const formValid = ref(false)
const form = ref<Required<ProductForm>>({
    name: '',
    quickDescription: '',
    description: '',
    weight: 0,
    price: 0,
    stock: 0,
    promotedImage: null,
    illustrations: [],
})

async function submit() {
    if (!csrfToken)
        return
    loading.value = true
    const formData = new FormData()
    formData.append('_method', 'PUT')
    formData.append('_token', csrfToken)
    formData.append('name', form.value.name)
    formData.append('quickDescription', form.value.quickDescription)
    formData.append('description', form.value.description)
    formData.append('weight', String(form.value.weight))
    formData.append('price', String(form.value.price))
    formData.append('stock', String(form.value.stock))

    if (form.value.promotedImage) {
        formData.append('promotedImage', form.value.promotedImage)
    }

    // products.update
    await fetch(route('products.update', { product: props.product.id }), {
        method: 'POST',
        body: formData,
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
            name: props.product.name,
            quickDescription: props.product.quickDescription,
            weight: props.product.weight,
            description: props.product.description,
            price: props.product.price.euros,
            stock: props.product.stock,
            promotedImage: null,
            illustrations: [],
        }

        previewUrl.value = props.product.promotedImage
    }
}, { immediate: true })
</script>

<template>
    <VDialog
        v-model="displayDialog"
        max-width="960"
        persistent
        scrollable
    >
        <VCard
            :loading="loading ? 'primary' : false"
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
