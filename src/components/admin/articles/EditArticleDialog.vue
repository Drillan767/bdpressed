<script setup lang="ts">
import type { ProductForm } from '@/types'
import type { SchemaType } from '@root/amplify/data/resource'
import useBuckets from '@/composables/buckets'
import validationConfig from '@/plugins/validationConfig'
import useProductsStore from '@/stores/productsStore'
import { useForm, useIsFormValid } from 'vee-validate'
import { ref, watch } from 'vue'

type Product = SchemaType<'Product'> & { id: string }

interface EditProductForm extends ProductForm {
    id: string
}

const emit = defineEmits<{
    (e: 'success'): void
}>()

const displayDialog = defineModel<boolean>({ required: true })
const editedProduct = defineModel<Product>('product', { required: true })

const { updateProduct } = useProductsStore()
const { getSingleItem, getItems } = useBuckets()

const loading = ref(false)
const displayPreview = ref(false)
const previewUrl = ref('')
const promotedPreview = ref('')
const imagesPreviews = ref<string[]>([])

const { defineField, handleSubmit, resetForm, setValues } = useForm<Required<EditProductForm>>({
    validationSchema: {
        name: 'required',
        quickDescription: 'required',
        description: 'required',
        price: 'required',
    },
    initialValues: {
        id: editedProduct.value.id,
        name: editedProduct.value.name,
        quickDescription: editedProduct.value.quickDescription,
        description: editedProduct.value.description,
        price: editedProduct.value.price,
    },
})

defineField('id')
const [name, nameProps] = defineField('name', validationConfig)
const [quickDescription, quickDescriptionProps] = defineField('quickDescription', validationConfig)
const [description, descriptionProps] = defineField('description', validationConfig)
const [images, imagesProps] = defineField('images', validationConfig)
const [promotedImage, promotedImageProps] = defineField('promotedImage', validationConfig)
const [price, priceProps] = defineField('price', validationConfig)

const formValid = useIsFormValid()

const submit = handleSubmit(async (form) => {
    loading.value = true
    await updateProduct(form)
    emit('success')
    loading.value = false

    closeDialog()
})

function closeDialog() {
    displayDialog.value = false
    resetForm()
}

function openPreview(url: string) {
    displayPreview.value = true
    previewUrl.value = url
}

function removeSingleImage(index: number) {
    imagesPreviews.value?.splice(index, 1)
}

watch(displayDialog, async (value) => {
    if (value) {
        setValues({
            id: editedProduct.value.id,
            name: editedProduct.value.name,
            quickDescription: editedProduct.value.quickDescription,
            description: editedProduct.value.description,
            price: editedProduct.value.price,
        })
        promotedPreview.value = await getSingleItem(editedProduct.value.promotedImage)
        imagesPreviews.value = await getItems(editedProduct.value.images)
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
                <VRow v-if="imagesPreviews.length > 0">
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
