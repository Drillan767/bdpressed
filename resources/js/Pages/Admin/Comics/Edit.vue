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

const loading = ref(false)
const drag = ref(false)
const tempFiles = ref<File[]>([])
const fileInput = ref<HTMLInputElement | null>(null)
const imagePreview = ref<string>()
const displayPreview = ref(false)
const existingPages = ref([...props.comic.pages])

/*
{
  title: "Comic 1",
  description: "Description du comic 1",
  instagram_url: "https://www.instagram.com/comic1",
  preview: File | undefined,
  images: [
    File,
  ],
  imagesDetails: [
    {
        name: 'image1',
        type: 'existing',
    },
    {
        name: File.name,
        type: 'new',
    },
    {
        name: 'image2',
        type: 'existing',
    },
    {
        name: 'image3',
        type: 'existing',
    },
  ]
}
*/

// Create a reactive array for the draggable component
const displayImages = ref<{ type: 'existing' | 'new', url: string, index: number }[]>([])

// Update displayImages whenever existingPages or images change
function updateDisplayImages() {
    displayImages.value = [
        ...existingPages.value.map((page, index) => ({
            type: 'existing',
            url: page.image,
            index,
        })),
        ...images.value.map((file, index) => ({
            type: 'new',
            url: URL.createObjectURL(file),
            index,
        })),
    ]
}

// Initialize display images
onMounted(() => {
    imagePreview.value = props.comic.preview
    updateDisplayImages()
})

const save = handleSubmit((_values) => {
    // TODO: Implement save logic
})

const saveAndPublish = handleSubmit((_values) => {
    // TODO: Implement save and publish logic
})

function handleImages(files: File | File[]) {
    if (!Array.isArray(files)) {
        images.value.push(files)
    }
    else {
        files.forEach((file) => {
            images.value.push(file)
        })
    }

    tempFiles.value = []
    updateDisplayImages()
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

function removeImage(index: number) {
    const item = displayImages.value[index]
    if (item.type === 'existing') {
        existingPages.value.splice(item.index, 1)
    }
    else {
        images.value.splice(item.index, 1)
    }
    updateDisplayImages()
}

function handleReorder(newOrder: { type: 'existing' | 'new', url: string, index: number }[]) {
    // Update existing pages
    const existingItems = newOrder.filter(item => item.type === 'existing')
    existingPages.value = existingItems.map(item =>
        existingPages.value[item.index],
    )

    // Update new files
    const newItems = newOrder.filter(item => item.type === 'new')
    images.value = newItems.map(item =>
        images.value[item.index],
    )

    // Update display images
    updateDisplayImages()
}
</script>

<template>
    <h1 class="mb-4">
        <VIcon icon="mdi-draw" />
        Modifier "{{ comic.title }}"
    </h1>
    <VCard>
        <template #text>
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
                            v-model="displayImages"
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
                                            :src="element.url"
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
                                                <VIcon>mdi-delete</VIcon>
                                            </VBtn>
                                        </VCardActions>
                                    </VCard>
                                </VCol>
                            </template>
                        </draggable>
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
                            @click="save"
                        >
                            Enregister comme brouillon
                        </VBtn>
                        <VBtn
                            color="secondary"
                            :disabled="loading || !isFormValid"
                            variant="flat"
                            @click="saveAndPublish"
                        >
                            Enregistrer et publier
                        </VBtn>
                    </VCol>
                </VRow>
            </VContainer>
        </template>
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
