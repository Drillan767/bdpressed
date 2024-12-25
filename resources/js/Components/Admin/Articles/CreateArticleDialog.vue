<script setup lang="ts">
import type { ProductForm } from '@/types'
import { inject, ref } from 'vue'
import ArticleForm from './ArticleForm.vue'

const emit = defineEmits<{
    (e: 'success'): void
}>()

const csrfToken = inject<string>('csrfToken')

const form = ref<Required<ProductForm>>({
    name: '',
    quickDescription: '',
    weight: 0,
    description: '',
    price: 0,
    promotedImage: null,
    illustrations: [],
})

const displayDialog = defineModel<boolean>({ required: true })

const formValid = ref(false)
const loading = ref(false)
const articleForm = ref<InstanceType<typeof ArticleForm>>()

async function submit() {
    if (!csrfToken)
        return
    loading.value = true
    const formData = new FormData()

    formData.append('name', form.value.name)
    formData.append('quickDescription', form.value.quickDescription)
    formData.append('description', form.value.description)
    formData.append('weight', String(form.value.weight))
    formData.append('price', String(form.value.price))

    form.value.illustrations.forEach((illustration) => {
        formData.append('illustrations[]', illustration)
    })

    formData.append('_token', csrfToken)

    await fetch('/administration/article', {
        method: 'POST',
        body: formData,
    })
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
            :loading="loading ? 'primary' : false"
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
