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

const { defineField, controlledValues, setErrors } = useForm<Form>({
    validationSchema: {
        title: 'required',
        description: 'required',
        instagram_url: 'required',
    },
})

const [title, titleProps] = defineField('title', validationConfig)
const [description, descriptionProps] = defineField('description', validationConfig)
const [instagramUrl, instagramUrlProps] = defineField('instagram_url', validationConfig)
const [images, imagesProps] = defineField('images', validationConfig)

const isFormValid = useIsFormValid()

/*
    TODO:
    - Create a ref of arrays
    - This array would contain the file, its name and its order
    - When the user upload a file, it should be added to the array
    - It would also not keep the uploaded file(s) in the input
    - When clicking on the image, it should open a dialog with the image and the ability to delete it

*/
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
                    v-bind="imagesProps"
                    v-model="images"
                    prepend-icon="mdi-image-multiple"
                    label="Images"
                    multiple
                />
            </VCol>
            <VCol>
                <draggable
                    v-model="images"
                    @start="drag = true"
                    @end="drag = false"
                >
                    <template #item="{ element }">
                        <VChip>
                            {{ element.name }}
                        </VChip>
                    </template>
                </draggable>
            </VCol>
        </VRow>
    </VContainer>
    {{ images }}
</template>
