<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue'
import validationConfig from '@/plugins/validationConfig'
import { router } from '@inertiajs/vue3'
import { useHead } from '@vueuse/head'
import { useForm, useIsFormValid } from 'vee-validate'
import { ref } from 'vue'
import draggable from 'vuedraggable'
import { route } from 'ziggy-js'

defineOptions({ layout: AdminLayout })

useHead({
    title: 'Nouvelle bédé',
})

interface Form {
    title: string
    description: string
    instagram_url: string
    preview: File
    is_published: boolean
    images: File[]
}

const loading = ref(false)
const drag = ref(false)
const tempFiles = ref<File[]>([])
const fileInput = ref<HTMLInputElement | null>(null)
const imagePreview = ref<string>()
const displayPreview = ref(false)

const { defineField, handleSubmit } = useForm<Form>({
    validationSchema: {
        title: 'required',
        preview: 'required',
        description: 'required',
        instagram_url: 'required',
    },
    initialValues: {
        images: [],
    },
})

const [title, titleProps] = defineField('title', validationConfig)
const [description, descriptionProps] = defineField('description', validationConfig)
const [instagramUrl, instagramUrlProps] = defineField('instagram_url', validationConfig)
const [preview, previewProps] = defineField('preview', validationConfig)
const [images] = defineField('images', validationConfig)

const isFormValid = useIsFormValid()

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
}

function removeImage(index: number) {
    images.value.splice(index, 1)
}

function getImageUrl(file: File): string {
    return URL.createObjectURL(file)
}

const save = handleSubmit((values) => {
    router.post(route('comics.store'), {
        ...values,
        is_published: false,
    })
})

const saveAndPublish = handleSubmit((values) => {
    router.post(route('comics.store'), {
        ...values,
        is_published: true,
    })
})

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

function openPreview() {
    displayPreview.value = true
}
</script>

<template>
    <h1 class="mb-4">
        <VIcon icon="mdi-draw" />
        Nouvelle bédé
    </h1>

    <VContainer>
        <VRow>
            <VCol>
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
                            <VCol cols="12" md="6">
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
                                            @click.stop.prevent="openPreview"
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
                            <VCol>
                                <p v-if="images.length > 0">
                                    Glisser / déposer les images pour changer l'ordre des cases.
                                </p>
                                <draggable
                                    v-model="images"
                                    item-key="name"
                                    class="v-row"
                                    @start="drag = true"
                                    @end="drag = false"
                                >
                                    <template #item="{ element, index }">
                                        <VCol cols="2">
                                            <VCard>
                                                <VImg
                                                    :src="getImageUrl(element)"
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
                                                        @click="removeImage(index)"
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
                </VCard>
            </VCol>
        </VRow>
    </VContainer>
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
