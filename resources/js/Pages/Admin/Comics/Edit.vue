<script setup lang="ts">
import type { Comic, ComicPage } from '@/types'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import validationConfig from '@/plugins/validationConfig'
import { router } from '@inertiajs/vue3'
import { useHead } from '@vueuse/head'
import { useForm, useIsFormValid } from 'vee-validate'
import { computed, onMounted, ref } from 'vue'
import draggable from 'vuedraggable'
import { route } from 'ziggy-js'

interface EditForm {
    title: string
    description: string
    instagram_url: string
    is_published: boolean
    preview: File
    images: File[]
}

interface NewImage {
    file: File
    id: string
    isNew: boolean
    image: string
}

defineOptions({ layout: AdminLayout })

const props = defineProps<{
    comic: Comic
}>()

const { defineField, handleSubmit } = useForm<EditForm>({
    validationSchema: {
        title: 'required',
        description: 'required',
        instagram_url: 'required',
    },
    initialValues: {
        title: props.comic.title,
        description: props.comic.description,
        instagram_url: props.comic.instagram_url,
        images: [],
    },
})

const [title, titleProps] = defineField('title', validationConfig)
const [description, descriptionProps] = defineField('description', validationConfig)
const [instagramUrl, instagramUrlProps] = defineField('instagram_url', validationConfig)
const [preview, previewProps] = defineField('preview', validationConfig)
const [images] = defineField('images', validationConfig)

const isFormValid = useIsFormValid()

useHead({
    title: () => `Modifier "${props.comic.title}"`,
})

const fileInput = ref<HTMLInputElement | null>(null)
const displayPreview = ref(false)
const loading = ref(false)
const drag = ref(false)
const tempFiles = ref<File[]>([])
const imagePreview = ref<string>()
const imagesList = ref<(NewImage | ComicPage)[]>([])

onMounted(() => {
    imagesList.value = props.comic.pages
    imagePreview.value = props.comic.preview
})

function handleImages() {

}

function removeImage(index: number) {

}

function handleReorder() {

}

function generatePreviewUrl(file: File | File[]) {
    if (!Array.isArray(file)) {
        imagePreview.value = URL.createObjectURL(file)
    }
    else {
        file.forEach((f) => {
            imagePreview.value = URL.createObjectURL(f)
        })
    }
}
</script>

<template>
    <h1 class="mb-4">
        <VIcon icon="mdi-draw" />
        Modifier "{{ comic.title }}"
    </h1>
    <VCard>
        <VContainer>
            <VRow>
                <VCol cols="12" md="6">
                    <VTextField
                        v-bind="titleProps"
                        v-model="title"
                        label="Titre"
                    />
                </VCol>
                <VCol cols="12" md="6">
                    <VTextField
                        v-bind="instagramUrlProps"
                        v-model="instagramUrl"
                        label="Lien vers Instagram"
                    />
                </VCol>
            </VRow>
            <VRow>
                <VCol>
                    <VFileInput
                        v-bind="previewProps"
                        v-model="preview"
                        :multiple="false"
                        prepend-icon="mdi-image-frame"
                        label="Image de présentation"
                        accept="image/*"
                        @update:model-value="generatePreviewUrl"
                    >
                        <template #append-inner>
                            <VAvatar
                                :image="imagePreview"
                                class="cursor-pointer"
                                @click.stop.prevent="displayPreview = true"
                            />
                        </template>
                    </VFileInput>
                </VCol>
                <VCol cols="12" md="6">
                    <VFileInput
                        ref="fileInput"
                        v-model="tempFiles"
                        :hint="images.length > 0 ? 'Ajouter d\'autres images pour les mettres à la suite de celles présentes' : undefined"
                        prepend-icon="mdi-image-multiple"
                        label="Images"
                        multiple
                        @update:model-value="handleImages"
                    />
                </VCol>
                <VCol cols="12">
                    <p v-if="images.length > 0">
                        Glisser / déposer les images pour changer l'ordre des cases.
                    </p>
                    <draggable
                        v-model="imagesList"
                        item-key="index"
                        class="v-row"
                        @start="drag = true"
                        @end="drag = false"
                        @update:model-value="handleReorder"
                    >
                        <template #item="{ element }">
                            <VCol cols="2">
                                <VCard>
                                    <VImg
                                        :src="element.image"
                                        :width="300"
                                        max-height="200"
                                        aspect-ratio="1/1"
                                        cover
                                    />
                                    <VCardActions>
                                        <VSpacer />
                                        <VBtn
                                            icon
                                            color="error"
                                            @click="removeImage(element.index)"
                                        >
                                            <VIcon icon="mdi-delete" />
                                        </VBtn>
                                    </VCardActions>
                                </VCard>
                            </VCol>
                        </template>
                    </draggable>
                </VCol>
            </VRow>
            <VRow>
                <VCol class="d-flex justify-end ga-2">
                    <VBtn
                        variant="text"
                        @click="router.visit(route('admin.comics.index'))"
                    >
                        Annuler
                    </VBtn>
                    <VBtn
                        :disabled="loading || !isFormValid"
                        variant="flat"
                    >
                        Enregister comme brouillon
                    </VBtn>
                    <VBtn
                        color="secondary"
                        :disabled="loading || !isFormValid"
                        variant="flat"
                    >
                        Enregistrer et publier
                    </VBtn>
                </VCol>
            </VRow>
        </VContainer>
    </VCard>
    <VDialog
        v-model="displayPreview"
        :width="800"
    >
        <VImg
            :src="imagePreview"
            max-height="80vh"
        />
    </VDialog>
</template>
