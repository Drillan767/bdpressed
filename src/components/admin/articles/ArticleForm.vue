<script setup lang="ts">
import type { ProductForm } from '@/types'
import validationConfig from '@/plugins/validationConfig'
import { useForm, useIsFormValid } from 'vee-validate'
import { computed, ref, watch } from 'vue'

interface Props {
    form: ProductForm
    formValid: boolean
}

const props = defineProps<Props>()

const emit = defineEmits<{
    (e: 'update:form', form: ProductForm): void
    (e: 'update:form-valid', valid: boolean): void
}>()

const { defineField, controlledValues, resetForm } = useForm<ProductForm>({
    validationSchema: {
        name: 'required',
        quickDescription: 'required',
        description: 'required',
        price: 'required',
        promotedImage: 'required',
    },
    initialValues: props.form,
})

const [name, nameProps] = defineField('name', validationConfig)
const [quickDescription, quickDescriptionProps] = defineField('quickDescription', validationConfig)
const [description, descriptionProps] = defineField('description', validationConfig)
const [images, imagesProps] = defineField('images', validationConfig)
const [promotedImage, promotedImageProps] = defineField('promotedImage', validationConfig)
const [price, priceProps] = defineField('price', validationConfig)

const formValid = useIsFormValid()

const displayPreview = ref(false)
const previewUrl = ref('')

const imagesPreviews = computed(() => {
    if (!images.value)
        return []

    return Array.from(images.value).map(image => URL.createObjectURL(image))
})

const promotedPreview = computed(() => {
    if (!promotedImage.value)
        return ''

    return URL.createObjectURL(promotedImage.value)
})

function removeSingleImage(index: number) {
    images.value?.splice(index, 1)
}

function openPreview(url: string) {
    displayPreview.value = true
    previewUrl.value = url
}

watch(formValid, valid => emit('update:form-valid', valid), { immediate: true })

watch(controlledValues, (values) => {
    emit('update:form', values)
})

defineExpose({
    resetForm,
})
</script>

<template>
    <VRow>
        <VCol>
            <VTextField
                v-bind="nameProps"
                v-model="name"
                label="Nom"
            />
        </VCol>
    </VRow>
    <VRow>
        <VCol cols="12" md="6">
            <VTextField
                v-bind="priceProps"
                v-model="price"
                prefix="€"
                label="Prix"
                hint="Utiliser un point comme séparateur."
                :step="0.01"
                :min="0"
                type="number"
                persistent-hint
            />
        </VCol>
        <VCol cols="12" md="6">
            <VFileInput
                v-bind="promotedImageProps"
                v-model="promotedImage"
                :multiple="false"
                prepend-icon="mdi-image-frame"
                label="Illustration principale"
                accept="image/*"
            >
                <template
                    v-if="promotedImage"
                    #append-inner
                >
                    <VAvatar
                        :image="promotedPreview"
                        class="cursor-pointer"
                        @click.stop.prevent="openPreview(promotedPreview)"
                    />
                </template>
            </VFileInput>
        </VCol>
    </VRow>
    <VRow>
        <VCol>
            <VTextField
                v-bind="quickDescriptionProps"
                v-model="quickDescription"
                label="Description courte"
            />
        </VCol>
    </VRow>
    <VRow>
        <VCol>
            <VTextarea
                v-bind="descriptionProps"
                v-model="description"
                label="Description"
            />
        </VCol>
    </VRow>
    <VRow>
        <VCol>
            <VFileInput
                v-bind="imagesProps"
                v-model="images"
                label="Illustrations"
                accept="image/*"
                prepend-icon="mdi-image-multiple"
                chips
                multiple
            />
        </VCol>
    </VRow>
    <VRow v-if="imagesPreviews.length">
        <VCol
            v-for="(preview, index) in imagesPreviews"
            :key="index"
            cols="12"
            md="3"
        >
            <VCard
                height="160"
                class="pa-2"
                variant="flat"
            >
                <VImg
                    :src="preview"
                    :alt="`Illustration ${index + 1}`"
                    class="rounded-lg cursor-pointer"
                    width="100%"
                    height="auto"
                    @click="openPreview(preview)"
                />
                <VBtn
                    variant="text"
                    icon="mdi-close"
                    color="error"
                    class="position-absolute top-0 right-0"
                    @click.stop="removeSingleImage(index)"
                />
            </VCard>
        </VCol>
    </VRow>
    <VDialog
        v-model="displayPreview"
        :width="800"
    >
        <VImg :src="previewUrl" />
    </VDialog>
</template>
