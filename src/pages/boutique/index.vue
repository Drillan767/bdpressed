<script setup lang="ts">
import BedeBlock from '@/components/visitors/BedeBlock.vue'
import useNumbers from '@/composables/numbers'
import useProductsStore from '@/stores/productsStore'
import { useHead } from '@vueuse/head'
import { onMounted, ref } from 'vue'

interface Product {
    id: string | null
    name: string
    slug: string
    price: number
    promotedImage: string
    quickDescription: string
}

const products = ref<Product[]>([])

useHead({
    title: 'Boutique',
})

const { getCatalog } = useProductsStore()
const { formatPrice } = useNumbers()

async function getProducts() {
    products.value = await getCatalog()
}

onMounted(getProducts)
</script>

<template>
    <VContainer>
        <VRow>
            <VCol>
                <BedeBlock>
                    <h1 class="text-center text-h4 mb-4 bd">
                        Mes petites créations à vendre :
                    </h1>
                    <h2 text-h5 class="text-center my-12 bd">
                        ¥ Tu reçois des payettes, je reçois de quoi payer ma psy ¥
                    </h2>

                    <VContainer>
                        <VRow>
                            <VCol
                                cols="12"
                                md="4"
                            >
                                <VCard
                                    variant="flat"
                                    to="/boutique/illustration"
                                >
                                    <VContainer class="pa-0 text-secondary">
                                        <VRow no-gutters>
                                            <VCol>
                                                <VImg
                                                    src="https://cdn.vuetifyjs.com/images/cards/sunshine.jpg"
                                                    alt="Illustration"
                                                    class="rounded-lg"
                                                    aspect-ratio="9/16"
                                                    width="100%"
                                                />
                                            </VCol>
                                        </VRow>
                                        <VRow>
                                            <VCol cols="10">
                                                <h3>Illustration</h3>
                                                <p>À partir de 25€</p>
                                            </VCol>
                                            <VCol cols="2">
                                                <VBtn
                                                    variant="text"
                                                    color="secondary"
                                                    icon="mdi-cart"
                                                />
                                            </VCol>
                                        </VRow>
                                        <VRow no-gutters>
                                            <VCol>
                                                <p>
                                                    Lorem ipsum dolor sit amet consectetur adipisicing elit. Quidem, doloremque.
                                                </p>
                                            </VCol>
                                        </VRow>
                                    </VContainer>
                                </VCard>
                            </VCol>
                            <VCol
                                v-for="(product, i) in products"
                                :key="i"
                                cols="12"
                                md="4"
                            >
                                <VCard
                                    :to="`/boutique/${product.slug}`"
                                    variant="flat"
                                >
                                    <VContainer class="pa-0 text-secondary">
                                        <VRow no-gutters>
                                            <VCol class="image-container">
                                                <img
                                                    :src="product.promotedImage"
                                                    :alt="product.name"
                                                >
                                            </VCol>
                                        </VRow>
                                        <VRow>
                                            <VCol cols="10">
                                                <h3>{{ product.name }}</h3>
                                                <p>{{ formatPrice(product.price) }}</p>
                                            </VCol>
                                            <VCol cols="2">
                                                <VBtn
                                                    variant="text"
                                                    color="secondary"
                                                    icon="mdi-cart"
                                                />
                                            </VCol>
                                        </VRow>
                                        <VRow no-gutters>
                                            <VCol>
                                                <p>
                                                    {{ product.quickDescription }}
                                                </p>
                                            </VCol>
                                        </VRow>
                                    </VContainer>
                                </VCard>
                            </VCol>
                        </VRow>
                    </VContainer>
                </BedeBlock>
            </VCol>
        </VRow>
    </VContainer>
</template>

<style lang="scss" scoped>
.image-container {
    position: relative;
    height: 500px;
    overflow: hidden;

    img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
    }
}
</style>
