<script setup lang="ts">
import type { SchemaType } from '@root/amplify/data/resource'
import DeleteArticleDialog from '@/components/admin/articles/DeleteArticleDialog.vue'
import useNumbers from '@/composables/numbers'
import useToast from '@/composables/toast'
import AdminLayout from '@/layouts/AdminLayout.vue'
import useProductsStore from '@/stores/productsStore'
import { getUrl } from 'aws-amplify/storage'
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useDisplay } from 'vuetify'

const { params: { id: articleId } } = useRoute<'/administration/articles/[id]'>()
const router = useRouter()

const { getSingleProduct } = useProductsStore()
const { showSucess } = useToast()
const { formatPrice } = useNumbers()
const { mobile } = useDisplay()

const product = ref<SchemaType<'Product'>>()
const imgUrls = ref<string[]>([])
const promotedImage = ref('')
const displayPreview = ref(false)
const previewUrl = ref('')
const displayDeleteDialog = ref(false)

const globalImageHeight = computed(() => mobile.value ? 150 : 300)

onMounted(async () => {
    const result = await getSingleProduct(articleId)

    if (result) {
        product.value = result
    }
})

async function loadPromotedImage() {
    if (!product.value)
        return
    const result = await getUrl({
        path: product.value.promotedImage,
    })

    promotedImage.value = result ? result.url.href : ''
}

async function loadImages() {
    if (!product.value)
        return
    const promises = product.value.images.map(async (image) => {
        const result = await getUrl({
            path: image,
        })

        return result ? result.url.href : ''
    })

    imgUrls.value = await Promise.all(promises)
}

function openPreview(url: string) {
    displayPreview.value = true
    previewUrl.value = url
}

function articleDeleted() {
    router.push('/administration/articles')
        .then(() => showSucess('L\'article a été supprimé'))
}

watch(product, () => {
    loadImages()
    loadPromotedImage()
})
</script>

<template>
    <AdminLayout>
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
                        @click="console.log('edit')"
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
                                    <p>
                                        {{ product.description }}
                                    </p>
                                </VCol>
                                <VCol cols="12" md="6">
                                    <p class="text-h6 text-center">
                                        Illustration principale
                                    </p>
                                    <VImg
                                        :src="promotedImage"
                                        :height="globalImageHeight"
                                        width="100%"
                                        class="rounded cursor-pointer"
                                        @click="openPreview(promotedImage)"
                                    />
                                </vcol>
                            </VRow>
                            <template v-if="product.images.length > 0">
                                <VDivider
                                    class="my-4"
                                />

                                <VRow>
                                    <VCol>
                                        <p class="text-h6">
                                            Illustrations
                                        </p>
                                    </VCol>
                                </VRow>
                                <VRow>
                                    <VCol
                                        v-for="(image, index) in imgUrls"
                                        :key="index"
                                        cols="12"
                                        md="3"
                                    >
                                        <VImg
                                            :src="image"
                                            :height="globalImageHeight"
                                            width="100%"
                                            class="rounded cursor-pointer"
                                            @click="openPreview(image)"
                                        />
                                    </VCol>
                                </VRow>
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
        <DeleteArticleDialog
            v-if="product"
            v-model="displayDeleteDialog"
            :product="product"
            @success="articleDeleted"
        />
    </AdminLayout>
</template>
