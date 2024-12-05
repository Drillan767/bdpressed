<script setup lang="ts">
import type { SchemaType } from '@root/amplify/data/resource'
import DeleteArticleDialog from '@/components/admin/articles/DeleteArticleDialog.vue'
import EditArticleDialog from '@/components/admin/articles/EditArticleDialog.vue'
import EditIllustrationsForm from '@/components/admin/articles/EditIllustrationsForm.vue'
import useBuckets from '@/composables/buckets'
import useNumbers from '@/composables/numbers'
import useStrings from '@/composables/strings'
import useToast from '@/composables/toast'
import useProductsStore from '@/stores/productsStore'
import { useHead } from '@vueuse/head'
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useDisplay } from 'vuetify'

type Product = SchemaType<'Product'> & { id: string }

const { params: { id: articleId } } = useRoute()
const router = useRouter()

const { getSingleProduct } = useProductsStore()
const { showSuccess } = useToast()
const { toParagraphs } = useStrings()
const { formatPrice } = useNumbers()
const { getSingleItem } = useBuckets()
const { mobile } = useDisplay()

const product = ref<Product>()
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
    const result = await getSingleProduct(articleId.toString())

    if (result) {
        product.value = result as Product
    }
}

async function loadPromotedImage() {
    if (!product.value)
        return

    promotedImage.value = await getSingleItem(product.value.promotedImage)
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

useHead({
    title: () => `${product.value?.name} | Article`,
})

watch(product, loadPromotedImage)
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
</template>

<style scoped>
    .description :deep(p) {
        margin-bottom: 1rem;
    }
</style>
