<script setup lang="ts">
import type { AdminProduct, Catalog } from '@/types'
import ProductIllustration from '@/Components/Shop/ProductIllustration.vue'
import useNumbers from '@/Composables/numbers'
import useStrings from '@/Composables/strings'
import VisitorsLayout from '@/Layouts/VisitorsLayout.vue'
import useCartStore from '@/Stores/cartStore'
import { useHead } from '@vueuse/head'
import { inject, onMounted, ref } from 'vue'

interface Props {
    product: AdminProduct
}

defineOptions({ layout: VisitorsLayout })

const props = defineProps<Props>()

const openDrawer = inject<() => void>('openDrawer')

const { formatPrice } = useNumbers()
const { toParagraphs } = useStrings()
const { addItem } = useCartStore()

onMounted(async () => {
    /* const result = await productBySlug(slug.toString())

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
    } */
})

function handleAddToCart(product: Catalog) {
    openDrawer?.()
    setTimeout(() => {
        addItem({
            id: product.id,
            name: product.name,
            weight: product.weight,
            price: product.price,
            illustration: product.promotedImage,
        })
    }, 200)
}

useHead({
    title: () => `Boutique | ${props.product.name}`,
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
                                        class="mb-2"
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
