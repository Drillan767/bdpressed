<script setup lang="ts">
import type { SchemaType } from '@root/amplify/data/resource'
import DeleteArticleDialog from '@/components/admin/articles/DeleteArticleDialog.vue'
import EditArticleDialog from '@/components/admin/articles/EditArticleDialog.vue'
import EditIllustrationsForm from '@/components/admin/articles/EditIllustrationsForm.vue'
import useNumbers from '@/composables/numbers'
import useStrings from '@/composables/strings'
import useToast from '@/composables/toast'
import AdminLayout from '@/layouts/AdminLayout.vue'
import useProductsStore from '@/stores/productsStore'
import { getUrl } from 'aws-amplify/storage'
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useDisplay } from 'vuetify'

type Product = SchemaType<'Product'> & { id: string }

const { params: { id: articleId } } = useRoute<'/administration/articles/[id]'>()
const router = useRouter()

const { getSingleProduct } = useProductsStore()
const { showSuccess } = useToast()
const { toParagraphs } = useStrings()
const { formatPrice } = useNumbers()
const { mobile } = useDisplay()

const product = ref<Product>()
const imgUrls = ref<string[]>([])
const promotedImage = ref('')
const displayPreview = ref(false)
const previewUrl = ref('')
const displayEditDialog = ref(false)
const displayDeleteDialog = ref(false)

const globalImageHeight = computed(() => mobile.value ? 150 : 300)

const formattedDescription = computed(() => {
    if (!product.value)
        return ''

    return toParagraphs(product.value.description)
})

onMounted(loadProduct)

async function loadProduct() {
    const result = await getSingleProduct(articleId)

    if (result) {
        product.value = result as Product
    }
}

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

async function articleUpdated() {
    await loadProduct()
    showSuccess('L\'article a été modifié avec succès.')
    displayEditDialog.value = false
}

function articleDeleted() {
    router.push('/administration/articles')
        .then(() => showSuccess('L\'article a été supprimé'))
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
                                        v-html="formattedDescription"
                                    />
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
                                    <VCol cols="12">
                                        <p class="text-h6">
                                            Illustrations
                                        </p>
                                    </VCol>
                                </VRow>

                                <EditIllustrationsForm
                                    v-model:images="product.images"
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
        <EditArticleDialog
            v-if="product"
            v-model="displayEditDialog"
            v-model:product="product"
            @success="articleUpdated"
        />
        <DeleteArticleDialog
            v-if="product"
            v-model="displayDeleteDialog"
            :product="product"
            @success="articleDeleted"
        />
    </AdminLayout>
</template>

<style scoped>
    .description :deep(p) {
        margin-bottom: 1rem;
    }
</style>
