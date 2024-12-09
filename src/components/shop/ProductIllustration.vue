<script setup lang="ts">
import { ref, useTemplateRef } from 'vue'

interface Props {
    illustration: {
        path: string
        type: string
    }
}

const { illustration } = defineProps<Props>()

const previewFile = ref<Props['illustration']>()
const displayPreview = ref(false)
const video = useTemplateRef<HTMLVideoElement>('video')

function openPreview(preview: Props['illustration']) {
    previewFile.value = preview
    displayPreview.value = true

    if (previewFile.value.type.includes('video')) {
        setInterval(() => {
            video.value?.play()
        }, 200)
    }
}
</script>

<template>
    <VHover>
        <template #default="{ isHovering, props }">
            <div
                v-bind="props"
                class="preview"
            >
                <div
                    class="media-container"
                >
                    <video
                        v-if="illustration.type.includes('video')"
                        class="media-item"
                        :src="`${illustration.path}`"
                        @click="openPreview(illustration)"
                    />

                    <img
                        v-else
                        :src="illustration.path"
                        alt="Illustration"
                        class="media-item"
                        width="100%"
                        height="auto"
                        @click="openPreview(illustration)"
                    >

                    <VOverlay
                        :model-value="isHovering ?? false"
                        contained
                        content-class="d-flex align-center justify-center h-100 w-100"
                        @click="openPreview(illustration)"
                    >
                        <VIcon
                            :icon="illustration.type.includes('video') ? 'mdi-play' : 'mdi-image'"
                            size="64"
                        />
                    </VOverlay>
                </div>
            </div>
        </template>
    </VHover>
    <VDialog
        v-model="displayPreview"
        :width="800"
    >
        <video
            v-if="previewFile && previewFile.type.includes('video')"
            ref="video"
            :src="previewFile.path"
            class="video-preview"
            controls
        />
        <VImg
            v-if="previewFile && previewFile.type.includes('image')"
            :src="previewFile.path"
            height="80vh"
        />
    </VDialog>
</template>

<style lang="scss" scoped>
.preview {
    height: 350px;
    display: flex;
    flex-direction: column;
    gap: 15px;

    .media-container {
        position: relative;
        width: 100%;
        height: 100%;
        overflow: hidden;
        border-radius: 10px;
        cursor: pointer;

        .media-item {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }
    }
}

.video-preview {
    max-height: 80vh;
}
</style>
