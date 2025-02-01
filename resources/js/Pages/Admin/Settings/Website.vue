<script setup lang="ts">
import useToast from '@/Composables/toast'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import SettingsLayout from '@/Layouts/SettingsLayout.vue'
import validationConfig from '@/plugins/validationConfig'
import { router } from '@inertiajs/vue3'
import { useHead } from '@vueuse/head'
import { useForm, useIsFormDirty, useIsFormValid } from 'vee-validate'
import { onMounted, ref, useAttrs, watch } from 'vue'
import { VTextField } from 'vuetify/components'
import { route } from 'ziggy-js'

interface Props {
    settings: {
        comics_image_url: string
        comics_text: string
        shop_title: string
        shop_subtitle: string
        contact_image_url: string
        contact_text: string
    }
    flash: {
        success: string | null
    }
}

interface SettingsProps {
    comics_image_url: File
    comics_text: string
    shop_title: string
    shop_subtitle: string
    contact_image_url: File
    contact_text: string
}

defineOptions({ layout: AdminLayout })

const props = defineProps<Props>()
useAttrs()

const { showSuccess } = useToast()

useHead({
    title: 'Paramètres du site',
})

const { defineField, handleSubmit } = useForm<SettingsProps>({
    validationSchema: {
        comics_text: 'required',
        shop_title: 'required',
        shop_subtitle: 'required',
        contact_text: 'required',
    },
    initialValues: {
        comics_text: props.settings.comics_text,
        shop_title: props.settings.shop_title,
        shop_subtitle: props.settings.shop_subtitle,
        contact_text: props.settings.contact_text,
    },
})

const [comicsImage, comicsImageProps] = defineField('comics_image_url', validationConfig)
const [comicsText, comicsTextProps] = defineField('comics_text', validationConfig)
const [shopTitle, shopTitleProps] = defineField('shop_title', validationConfig)
const [shopSubtitle, shopSubtitleProps] = defineField('shop_subtitle', validationConfig)
const [contactImage, contactImageProps] = defineField('contact_image_url', validationConfig)
const [contactText, contactTextProps] = defineField('contact_text', validationConfig)

const formValid = useIsFormValid()
const formDirty = useIsFormDirty()

const displayPreview = ref(false)
const previewUrl = ref<string>()
const comicsPreview = ref('')
const contactPreview = ref('')

const submit = handleSubmit(async (form) => {
    router.post(route('settings.website.update'), {
        ...form,
    })
})

function openPreview(preview: string) {
    previewUrl.value = preview
    displayPreview.value = true
}

async function generatePreviewUrl(file: File | File[], field: 'comics' | 'contact') {
    if (Array.isArray(file))
        return

    if (field === 'comics') {
        comicsPreview.value = URL.createObjectURL(file)
    }
    else {
        contactPreview.value = URL.createObjectURL(file)
    }
}

onMounted(() => {
    comicsPreview.value = props.settings.comics_image_url
    contactPreview.value = props.settings.contact_image_url
})

watch(() => props.flash.success, (value) => {
    if (value)
        showSuccess(value)
}, { immediate: true })
</script>

<template>
    <SettingsLayout>
        <VCard
            class="mt-4"
            variant="outlined"
            title="Bédés"
        >
            <template #text>
                <VContainer>
                    <VRow>
                        <VCol cols="12" md="6">
                            <VFileInput
                                v-bind="comicsImageProps"
                                v-model="comicsImage"
                                label="Image pour les bédés"
                                prepend-icon="mdi-image"
                                accept="image/*"
                                @update:model-value="($e) => generatePreviewUrl($e, 'comics')"
                            >
                                <template #append-inner>
                                    <VAvatar
                                        :image="comicsPreview"
                                        class="cursor-pointer"
                                        @click.stop.prevent="openPreview(comicsPreview)"
                                    />
                                </template>
                            </VFileInput>
                        </VCol>
                        <VCol cols="12" md="6">
                            <VTextarea
                                v-bind="comicsTextProps"
                                v-model="comicsText"
                                label="Texte des bédés"
                            />
                        </vcol>
                    </VRow>
                </VContainer>
            </template>
        </VCard>
        <VCard
            class="mt-4"
            variant="outlined"
            title="Boutique"
        >
            <template #text>
                <VContainer>
                    <VRow>
                        <VCol cols="12" md="6">
                            <VTextField
                                v-bind="shopTitleProps"
                                v-model="shopTitle"
                                label="Titre de la page"
                            />
                        </VCol>
                        <VCol cols="12" md="6">
                            <VTextField
                                v-bind="shopSubtitleProps"
                                v-model="shopSubtitle"
                                label="Sous-titre de la page"
                            />
                        </vcol>
                    </VRow>
                </VContainer>
            </template>
        </VCard>
        <VCard
            class="mt-4"
            variant="outlined"
            title="Contact"
        >
            <template #text>
                <VContainer>
                    <VRow>
                        <VCol cols="12" md="6">
                            <VFileInput
                                v-bind="contactImageProps"
                                v-model="contactImage"
                                label="Image de la page contact"
                                prepend-icon="mdi-image"
                                accept="image/*"
                                @update:model-value="($e) => generatePreviewUrl($e, 'contact')"
                            >
                                <template #append-inner>
                                    <VAvatar
                                        :image="contactPreview"
                                        class="cursor-pointer"
                                        @click.stop.prevent="openPreview(contactPreview)"
                                    />
                                </template>
                            </VFileInput>
                        </VCol>
                        <VCol cols="12" md="6">
                            <VTextarea
                                v-bind="contactTextProps"
                                v-model="contactText"
                                label="Texte de la page contact"
                            />
                        </vcol>
                    </VRow>
                </VContainer>
            </template>
        </VCard>
        <VRow class="mt-4">
            <VCol class="text-end">
                <VBtn
                    :disabled="!formDirty || !formValid"
                    @click="submit"
                >
                    Enregistrer
                </VBtn>
            </VCol>
        </VRow>
    </SettingsLayout>
    <VDialog
        v-model="displayPreview"
        :width="800"
    >
        <VImg :src="previewUrl" />
    </VDialog>
</template>
