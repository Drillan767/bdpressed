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
                                v-for="(product, i) in products"
                                :key="i"
                                cols="12"
                                md="4"
                            >
                                <p>{{ product.name }}</p>
                            </VCol>
                        </VRow>
                    </VContainer>
                </BedeBlock>
            </VCol>
        </VRow>
    </VContainer>
</template>
