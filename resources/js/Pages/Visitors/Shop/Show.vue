<script setup lang="ts">
import type { AdminProduct, Catalog } from '@/types'
import ProductIllustration from '@/Components/Shop/ProductIllustration.vue'
import useNumbers from '@/Composables/numbers'
import useStrings from '@/Composables/strings'
import VisitorsLayout from '@/Layouts/VisitorsLayout.vue'
import useCartStore from '@/Stores/cartStore'
import { Link } from '@inertiajs/vue3'
import { useHead } from '@vueuse/head'
import { inject } from 'vue'
import { route } from 'ziggy-js'

interface Props {
    product: AdminProduct
}

defineOptions({ layout: VisitorsLayout })

const props = defineProps<Props>()

const openDrawer = inject<() => void>('openDrawer')

const { formatPrice } = useNumbers()
const { toParagraphs } = useStrings()
const { addItem } = useCartStore()

function handleAddToCart(product: Catalog) {
    openDrawer?.()
    setTimeout(() => {
        addItem({
            id: product.id,
            name: product.name,
            weight: product.weight,
            stock: product.stock - 1,
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
            <VAlert
                v-if="product.stock === 0"
                color="primary"
                variant="outlined"
            >
                Je n'ai plus cet article pour le moment, et normalement, il est en cours de réapprovisionnement ! Mais si
                vous trouvez que ça fait un moment qu'il n'est plus dispo, n'hésitez pas à venir râler
                <Link
                    :href="route('contact')"
                    class="text-secondary"
                >
                    ici
                </Link>
            </VAlert>
            <VContainer v-if="product">
                <VRow>
                    <VCol
                        cols="12"
                        md="6"
                        class="image-container border-xl pa-4"
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
                                    :disabled="product.stock === 0"
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

<style scoped lang="scss">
:deep(.v-alert__content) {
    padding: 8px 0;
}
</style>
