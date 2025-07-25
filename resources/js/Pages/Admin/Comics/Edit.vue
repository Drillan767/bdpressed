<script setup lang="ts">
import type { Comic, ComicPage } from '@/types'
import useToast from '@/Composables/toast'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import validationConfig from '@/plugins/validationConfig'
import { router } from '@inertiajs/vue3'
import { useHead } from '@vueuse/head'
import { useForm, useIsFormValid } from 'vee-validate'
import { onMounted, ref, watch } from 'vue'
import draggable from 'vuedraggable'
import { route } from 'ziggy-js'

interface EditForm {
    title: string
    description: string
    instagram_url: string
    is_published: boolean
    preview: File
}

interface ImageItem extends ComicPage {
    isNew?: boolean
    file?: File
}

defineOptions({ layout: AdminLayout })

const props = defineProps<{
    comic: Comic
    flash: {
        success: string | null
    }
}>()

const { defineField, handleSubmit, resetForm } = useForm<EditForm>({
    validationSchema: {
        title: 'required',
        description: 'required',
        instagram_url: 'required',
    },
    initialValues: {
        title: props.comic.title,
        description: props.comic.description,
        instagram_url: props.comic.instagram_url,
    },
})

const [title, titleProps] = defineField('title', validationConfig)
const [description, descriptionProps] = defineField('description', validationConfig)
const [instagramUrl, instagramUrlProps] = defineField('instagram_url', validationConfig)
const [preview, previewProps] = defineField('preview', validationConfig)

const isFormValid = useIsFormValid()
const { showSuccess } = useToast()

useHead({
    title: () => `Modifier "${props.comic.title}"`,
})

const fileInput = ref<HTMLInputElement | null>(null)
const displayPreview = ref(false)
const loading = ref(false)
const drag = ref(false)
const displayDeleteDialog = ref(false)
const tempFiles = ref<File[]>([])
const imagePreview = ref<string>()
const imgIndex = ref<number>()
const deletedPages = ref<ComicPage[]>([])
const imagesList = ref<ImageItem[]>([])

onMounted(() => {
    imagesList.value = props.comic.pages.map(page => ({
        ...page,
        isNew: false,
    }))
    imagePreview.value = props.comic.preview
})

function handleImages(files: File | File[]) {
    if (!Array.isArray(files)) {
        imagesList.value.push({
            file: files,
            id: imagesList.value.length + 1,
            isNew: true,
            image: URL.createObjectURL(files),
            comic_id: props.comic.id,
            order: imagesList.value.length + 1,
        } as ImageItem)
    }
    else {
        files.forEach((file, i) => {
            imagesList.value.push({
                file,
                id: imagesList.value.length + 1 + i,
                isNew: true,
                image: URL.createObjectURL(file),
                comic_id: props.comic.id,
                order: imagesList.value.length + 1,
            } as ImageItem)
        })
    }
}

function removeImage(page: ImageItem, index: number) {
    if (page.isNew) {
        imagesList.value = imagesList.value.filter(p => p.id !== page.id)
    }
    else {
        imgIndex.value = index
        displayDeleteDialog.value = true
    }
}

function handleDeletePage() {
    if (imgIndex.value === undefined)
        return

    const pageToDelete = imagesList.value[imgIndex.value]
    if (!pageToDelete.isNew) {
        deletedPages.value.push(pageToDelete as ComicPage)
    }
    imagesList.value = imagesList.value.filter((_, index) => index !== imgIndex.value)
    displayDeleteDialog.value = false
    imgIndex.value = undefined
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

function handlePublish() {
    router.post(route('admin.comics.toggle-publish', props.comic.id))
}

function reset() {
    resetForm()
    imagesList.value = props.comic.pages.map(page => ({
        ...page,
        isNew: false,
    }))
    imagePreview.value = props.comic.preview
    deletedPages.value = []
}

function updateOrder() {
    drag.value = false
    // Update order based on current array position
    imagesList.value.forEach((item, index) => {
        item.order = index + 1
    })
}

const submitForm = handleSubmit(async (form) => {
    loading.value = true

    // Create FormData for file upload
    const formData = new FormData()
    formData.append('title', form.title)
    formData.append('description', form.description)
    formData.append('instagram_url', form.instagram_url)

    if (form.preview) {
        formData.append('preview', form.preview)
    }

    // Add new images
    const newImages = imagesList.value.filter(img => img.isNew && img.file)
    newImages.forEach((img, index) => {
        if (img.file) {
            formData.append(`new_images[${index}]`, img.file)
            formData.append(`new_images_order[${index}]`, img.order.toString())
        }
    })

    // Add existing images order
    const existingImages = imagesList.value.filter(img => !img.isNew)
    existingImages.forEach((img, index) => {
        formData.append(`existing_images[${index}]`, img.id.toString())
        formData.append(`existing_images_order[${index}]`, img.order.toString())
    })

    // Add deleted pages
    deletedPages.value.forEach((page, index) => {
        formData.append(`deleted_pages[${index}]`, page.id.toString())
    })

    formData.append('_method', 'PUT')
    router.post(route('admin.comics.update', props.comic.slug), formData, {
        onSuccess: () => {
            loading.value = false
        },
        onError: () => {
            loading.value = false
        },
    })
})

watch(() => props.flash.success, (value) => {
    if (value)
        showSuccess(value)
}, { immediate: true })
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
                        :hint="imagesList.length > 0 ? 'Ajouter d\'autres images pour les mettres à la suite de celles présentes' : undefined"
                        prepend-icon="mdi-image-multiple"
                        label="Images"
                        multiple
                        @update:model-value="handleImages"
                    />
                </VCol>
                <VCol cols="12">
                    <p v-if="imagesList.length > 0">
                        Glisser / déposer les images pour changer l'ordre des cases.
                    </p>
                    <draggable
                        v-model="imagesList"
                        item-key="id"
                        class="v-row"
                        @start="drag = true"
                        @end="updateOrder"
                    >
                        <template #item="{ element, index }">
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
                                            @click="removeImage(element, index)"
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
                    <VBtn
                        color="secondary"
                        variant="flat"
                        @click="handlePublish"
                    >
                        {{ comic.is_published ? 'Dépublier' : 'Publier' }}
                    </VBtn>
                </VCol>
                <VSpacer />
                <VCol class="d-flex justify-end ga-2">
                    <VBtn
                        variant="text"
                        @click="router.visit(route('admin.comics.index'))"
                    >
                        Annuler
                    </VBtn>
                    <VBtn
                        variant="text"
                        color="secondary"
                        @click="reset"
                    >
                        Réinitialiser
                    </VBtn>
                    <VBtn
                        :disabled="loading || !isFormValid"
                        variant="flat"
                        @click="submitForm"
                    >
                        Mettre à jour
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
    <VDialog
        v-model="displayDeleteDialog"
        :width="500"
    >
        <VCard
            title="Supprimer la page ?"
        >
            <template #text>
                La page ne sera pas supprimée tant que les modifications ne seront pas enregistrées.<br>
                Cependant, elle ne sera plus visible dans le formulaire. Pour revenir en arrière, cliquez sur "Annuler" ou "Réinitialiser".
            </template>
            <template #actions>
                <VBtn
                    color="error"
                    @click="handleDeletePage"
                >
                    Supprimer
                </VBtn>
            </template>
        </VCard>
    </VDialog>
</template>
