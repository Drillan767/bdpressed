<script setup lang="ts">
import type { VFileInput } from 'vuetify/components'
import useBuckets from '@/composables/buckets'
import useToast from '@/composables/toast'
import useProductsStore from '@/stores/productsStore'
import { onMounted, ref, useTemplateRef } from 'vue'

interface Props {
    images: string[]
    productId: string
}

const props = defineProps<Props>()

const { getItems } = useBuckets()
const { updateProductImages, removeProductImages } = useProductsStore()
const { showSuccess } = useToast()

const fileInput = useTemplateRef<VFileInput>('fileInput')

const previewUrl = ref('')
const displayPreview = ref(false)
const imagesPreviews = ref<string[]>([])

function openPreview(url: string) {
    previewUrl.value = url
    displayPreview.value = true
}

function handleSelectImage() {
    fileInput.value?.click()
}

async function handleUploadedImages(files: File | File[]) {
    if (Array.isArray(files)) {
        const newList = await updateProductImages(files, props.productId)
        if (newList && newList.length > 0) {
            imagesPreviews.value = newList
        }
    }
    else {
        const newList = await updateProductImages([files], props.productId)
        if (newList && newList.length > 0) {
            imagesPreviews.value = newList
        }
    }

    showSuccess('Les nouvelles images ont été ajoutées avec succès.')
}

async function removeSingleImage(path: string) {
    const newList = await removeProductImages(path, props.productId)

    if (newList && newList.length > 0) {
        imagesPreviews.value = newList
    }
}

onMounted(async () => {
    imagesPreviews.value = await getItems(props.images)
})
</script>

<template>
    <VRow v-if="imagesPreviews.length > 0">
        <VCol
            v-for="(preview, index) in imagesPreviews"
            :key="index"
            cols="12"
            md="3"
        >
            <VCard
                height="210"
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

                <VCardActions>
                    <VBtn
                        variant="outlined"
                        color="error"
                        prepend-icon="mdi-trash-can-outline"
                        block
                        @click="removeSingleImage(preview)"
                    >
                        Supprimer
                    </VBtn>
                </VCardActions>
            </VCard>
        </VCol>
        <VCol
            cols="12"
            md="3"
        >
            <VCard
                height="190"
                variant="flat"
                @click="handleSelectImage"
            >
                <template #text>
                    <VEmptyState
                        text="Ajouter des illustrations"
                        class="border-sm border-primary rounded-lg"
                    >
                        <template #media>
                            <VIcon
                                icon="mdi-image-plus-outline"
                                color="primary"
                            />
                        </template>
                    </VEmptyState>
                    <VFileInput
                        ref="fileInput"
                        multiple
                        class="d-none"
                        @update:model-value="handleUploadedImages"
                    />
                </template>
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
