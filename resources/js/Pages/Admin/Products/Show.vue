<script setup lang="ts">
import type { AdminProduct } from '@/types'
import EditIllustrationsForm from '@/Components/Admin/Articles/EditIllustrationsForm.vue'
import useNumbers from '@/Composables/numbers'
import useStrings from '@/Composables/strings'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useDisplay } from 'vuetify'
import { ref } from 'vue'
import { useHead } from '@vueuse/head'

interface Props {
    product: AdminProduct
}

const { formatPrice } = useNumbers()
const { toParagraphs } = useStrings()

const promotedImage = ref('')
const displayPreview = ref(false)
const previewUrl = ref('')
const displayEditDialog = ref(false)
const displayDeleteDialog = ref(false)

const props = defineProps<Props>()

const { mobile } = useDisplay()

function openPreview(url: string) {
    displayPreview.value = true
    previewUrl.value = url
}
defineOptions({ layout: AdminLayout })
useHead({
    title: () => props.product.name,
})
</script>

<template>
    <VContainer>
        <VRow>
            <VCol
                v-if="product"
                cols="12"
                md="8"
            >
                <h1>
                    <VIcon icon="mdi-package-variant" />
                    {{ product.name }} ({{ formatPrice(product.price) }})
                </h1>
            </VCol>
            <VCol
                cols="12"
                md="4"
                class="text-end"
            >
                <VBtn
                    variant="outlined"
                    icon="mdi-pencil"
                    @click="displayEditDialog = true"
                />
                <VBtn
                    variant="outlined"
                    icon="mdi-delete"
                    class="ml-2"
                    color="error"
                    @click="displayDeleteDialog = true"
                />
            </VCol>
        </VRow>
        <VRow>
            <VCol>
                <VCard v-if="product">
                    <template #text>
                        <VRow>
                            <VCol cols="12" md="6">
                                <p class="text-h6">
                                    Description rapide
                                </p>
                                <p>
                                    {{ product.quickDescription }}
                                </p>
                                <VDivider class="my-4" />
                                <p class="text-h6">
                                    Description
                                </p>
                                <div
                                    class="text-body-2 description"
                                    v-html="toParagraphs(product.description)"
                                />
                            </VCol>
                            <VCol cols="12" md="6">
                                <p class="text-h6 text-center">
                                    Illustration principale
                                </p>
                                <VImg
                                    :src="product.promotedImage"
                                    :height="globalImageHeight"
                                    class="rounded-lg cursor-pointer"
                                    @click="openPreview(promotedImage)"
                                />
                            </vcol>
                        </VRow>
                        <template v-if="product.illustrations.length > 0">
                            <VDivider
                                class="my-4"
                            />

                            <VRow>
                                <VCol cols="12">
                                    <p class="text-h6">
                                        Illustrations
                                    </p>
                                </VCol>
                            </VRow>

                            <EditIllustrationsForm
                                v-model:illustrations="product.illustrations"
                                :product-id="product.id"
                            />
                        </template>
                    </template>
                </VCard>
            </VCol>
        </VRow>
    </VContainer>
    <VDialog
        v-model="displayPreview"
        :width="800"
    >
        <VImg :src="previewUrl" />
    </VDialog>
</template>

<style scoped>
.description :deep(p) {
    margin-bottom: 1rem;
}
</style>
