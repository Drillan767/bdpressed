<script setup lang="ts">
import type { VFileInput } from 'vuetify/components'
import useBuckets from '@/composables/buckets'
import useToast from '@/composables/toast'
import useProductsStore from '@/stores/productsStore'
import { onMounted, ref, useTemplateRef } from 'vue'

interface FileProperty {
    path: string
    type: string
}

interface Props {
    illustrations: string[]
    productId: string
}

const props = defineProps<Props>()

const { getItems } = useBuckets()
const { updateProductMedia, removeProductMedia } = useProductsStore()
const { showSuccess } = useToast()

const fileInput = useTemplateRef<VFileInput>('fileInput')
const video = useTemplateRef<HTMLVideoElement>('video')

const previewFile = ref<FileProperty>()
const displayPreview = ref(false)
const imagesPreviews = ref<FileProperty[]>([])

function openPreview(preview: FileProperty) {
    previewFile.value = preview
    displayPreview.value = true

    if (previewFile.value.type.includes('video')) {
        setInterval(() => {
            video.value?.play()
        }, 200)
    }
}

function handleSelectImage() {
    fileInput.value?.click()
}

async function handleUploadedImages(files: File | File[]) {
    if (Array.isArray(files)) {
        const newList = await updateProductMedia(files, props.productId)
        if (newList && newList.length > 0) {
            imagesPreviews.value = newList
        }
    }
    else {
        const newList = await updateProductMedia([files], props.productId)
        if (newList && newList.length > 0) {
            imagesPreviews.value = newList
        }
    }

    showSuccess('Les nouvelles images ont été ajoutées avec succès.')
}

async function removeSingleImage(index: number) {
    const newList = await removeProductMedia(props.illustrations[index], props.productId)

    if (newList && newList.length > 0) {
        imagesPreviews.value = newList
    }
}

onMounted(async () => {
    imagesPreviews.value = await getItems(props.illustrations)
})
</script>

<template>
    <!--     <VImg
        v-for="(image, i) in thumbnails"
        :key="i"
        :src="image"
    /> -->
    <VRow v-if="imagesPreviews.length > 0">
        <VCol
            v-for="(preview, index) in imagesPreviews"
            :key="index"
            cols="12"
            md="3"
        >
            <VCard
                height="320"
                class="pa-2"
                variant="flat"
            >
                <div
                    v-if="preview.type.includes('video')"
                    class="video-container"
                >
                    <div class="responsive-sizer" />
                    <video
                        class="preview-video"
                        :src="preview.path"
                        @click="openPreview(preview)"
                    />
                </div>

                <VImg
                    v-else
                    :src="preview.path"
                    :alt="`Illustration ${index + 1}`"
                    class="rounded-lg cursor-pointer preview"
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
                        @click="removeSingleImage(index)"
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
        <video
            v-if="previewFile && previewFile.type.includes('video')"
            ref="video"
            :src="previewFile.path"
            controls
        />
        <VImg
            v-if="previewFile && previewFile.type.includes('image')"
            :src="previewFile.path"
        />
    </VDialog>
</template>

<style lang="css" scoped>
.video-container {
    height: auto;
    width: 100%;

    display: flex;
    max-height: 100%;
    max-width: 100%;
    overflow: hidden;
    position: relative;

}

.preview-video {
    max-width: 100%;
    object-fit: cover;
    border-radius: 8px;
}

.responsive-sizer {
    padding-bottom: 75%;
    flex: 1 0 0;
}
</style>
