<script setup lang="ts">
import type { Catalog, VisitorProduct } from '@/types'
import ProductIllustration from '@/components/shop/ProductIllustration.vue'
import useNumbers from '@/composables/numbers'
import useStrings from '@/composables/strings'
import useCartStore from '@/stores/cartStore'
import useProductsStore from '@/stores/productsStore'
import { useHead } from '@vueuse/head'
import { inject, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const openDrawer = inject<() => void>('openDrawer')

const route = useRoute()
const router = useRouter()

const { params: { slug } } = route
const { productBySlug } = useProductsStore()
const { formatPrice } = useNumbers()
const { toParagraphs } = useStrings()
const { addItem } = useCartStore()

const product = ref<VisitorProduct>()

onMounted(async () => {
    const result = await productBySlug(slug.toString())

    if (result) {
        product.value = result
    }
    else {
        router.push({
            name: 'NotFound',
            params: { pathMatch: route.path.substring(1).split('/') },
            query: route.query,
            hash: route.hash,
        })
    }
})

function handleAddToCart(product: Catalog) {
    openDrawer?.()
    setTimeout(() => {
        addItem({
            id: product.id,
            name: product.name,
            price: product.price,
            illustration: product.promotedImage,
        })
    }, 200)
}

useHead({
    title: () => `Boutique | ${product.value?.name}`,
})
</script>

<template>
    <VCard class="bede-block">
        <VCardText class="bede-text">
            <VContainer v-if="product">
                <VRow>
                    <VCol
                        cols="12"
                        md="6"
                        class="image-container borde-xl pa-4"
                    >
                        <VImg
                            :src="product.promotedImage"
                            alt="Illustration"
                        />
                    </VCol>
                    <VCol
                        cols="12"
                        md="6"
                    >
                        <VRow no-gutters>
                            <VCol cols="12" md="8">
                                <h1>{{ product.name }}</h1>
                                <p>Prix: {{ formatPrice(product.price) }}</p>
                            </VCol>
                            <VCol
                                cols="12"
                                md="4"
                                class="d-flex justify-center mb-4 mb-md-0"
                            >
                                <VBtn
                                    variant="outlined"
                                    color="secondary"
                                    stacked
                                    @click="handleAddToCart(product)"
                                >
                                    <VIcon
                                        size="x-small"
                                        icon="mdi-cart-arrow-down"
                                    />
                                    Ajouter
                                </VBtn>
                            </VCol>
                        </VRow>
                        <VRow no-gutters>
                            <VCol>
                                <p>{{ product.quickDescription }}</p>
                            </VCol>
                        </VRow>
                    </VCol>
                </VRow>
                <VRow>
                    <VCol>
                        <h2 class="mb-2">
                            Informations
                        </h2>
                        <div v-html="toParagraphs(product.description)" />
                    </VCol>
                </VRow>
                <VRow v-if="product.illustrations.length > 0">
                    <VCol>
                        <h2 class="mb-2">
                            Illustrations
                        </h2>

                        <VRow>
                            <VCol
                                v-for="(illustration, i) in product.illustrations"
                                :key="i"
                                cols="12"
                                md="4"
                            >
                                <ProductIllustration :illustration />
                            </VCol>
                        </VRow>
                    </VCol>
                </VRow>
            </VContainer>
        </VCardText>
    </VCard>
</template>
