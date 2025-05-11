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
        images: [],
    },
})

const [title, titleProps] = defineField('title', validationConfig)
const [description, descriptionProps] = defineField('description', validationConfig)
const [instagramUrl, instagramUrlProps] = defineField('instagram_url', validationConfig)
const [preview, previewProps] = defineField('preview', validationConfig)
const [images] = defineField('images', validationConfig)

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
const imagesList = ref<(NewImage | ComicPage)[]>([])

onMounted(() => {
    imagesList.value = props.comic.pages
    imagePreview.value = props.comic.preview
})

function handleImages(files: File | File[]) {
    if (!Array.isArray(files)) {
        imagesList.value.push({
            file: files,
            id: Date.now().toString(),
            isNew: true,
            image: URL.createObjectURL(files),
        })
    }
    else {
        files.forEach((file, i) => {
            imagesList.value.push({
                file,
                id: Date.now().toString() + i,
                isNew: true,
                image: URL.createObjectURL(file),
            })
        })
    }
}

function removeImage(page: ComicPage | NewImage, index: number) {
    if (Object.hasOwn(page, 'isNew')) {
        imagesList.value = imagesList.value.filter(p => p.id !== page.id)
    }
    else {
        imgIndex.value = index
        displayDeleteDialog.value = true
    }
}

function handleDeletePage() {
    if (!imgIndex.value)
        return

    deletedPages.value.push(imagesList.value[imgIndex.value])
    imagesList.value = imagesList.value.filter(p => p.id !== deletedPages.value[0].id)
    displayDeleteDialog.value = false
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
    imagesList.value = props.comic.pages
    imagePreview.value = props.comic.preview
}

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
                                            @click="removeImage(element, element.index)"
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
