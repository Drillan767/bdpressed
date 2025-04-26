<script setup lang="ts">
import validationConfig from '@/plugins/validationConfig'
import { useForm, useIsFormValid } from 'vee-validate'
import { ref } from 'vue'
import draggable from 'vuedraggable'

interface Form {
    title: string
    description: string
    instagram_url: string
    images: File[]
}

const drag = ref(false)
const tempFiles = ref<File[]>([])
const fileInput = ref<HTMLInputElement | null>(null)

const { defineField, controlledValues, setErrors } = useForm<Form>({
    validationSchema: {
        title: 'required',
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
const [images, imagesProps] = defineField('images', validationConfig)

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
</script>

<template>
    <VContainer>
        <VRow>
            <VCol>
                <VTextField
                    v-bind="titleProps"
                    v-model="title"
                    label="Titre"
                />
            </VCol>
        </VRow>
        <VRow>
            <VCol>
                <VTextField
                    v-bind="instagramUrlProps"
                    v-model="instagramUrl"
                    label="Lien vers Instagram"
                />
            </VCol>
        </VRow>
        <VRow>
            <VCol cols="12">
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
    </VContainer>
</template>
