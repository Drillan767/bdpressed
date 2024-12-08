<script setup lang="ts">
import BedeBlock from '@/components/visitors/BedeBlock.vue'
import useNumbers from '@/composables/numbers'
import useStrings from '@/composables/strings'
import useProductsStore from '@/stores/productsStore'
import { useHead } from '@vueuse/head'
import { storeToRefs } from 'pinia'
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()

const { params: { slug } } = route
const { productBySlug } = useProductsStore()
const { productsLoading } = storeToRefs(useProductsStore())
const { formatPrice } = useNumbers()
const { toParagraphs } = useStrings()
const product = ref<any>()

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

useHead({
    title: () => `Boutique | ${product.value?.name}`,
})
</script>

<template>
    <BedeBlock>
        <VContainer v-if="product">
            <VRow>
                <VCol
                    cols="12"
                    md="6"
                    class="image-container"
                >
                    <img :src="product.promotedImage" alt="Illustration">
                </VCol>
                <VCol
                    cols="12"
                    md="6"
                >
                    <h1>{{ product.name }}</h1>
                    <p>{{ product.quickDescription }}</p>
                    <p>Prix: {{ formatPrice(product.price) }}</p>
                    <VBtn
                        variant="outlined"
                        color="secondary"
                        prepend-icon="mdi-cart"
                        block
                    >
                        Ajouter au panier
                    </VBtn>
                </VCol>
            </VRow>
            <VRow>
                <VCol>
                    <h2>Informations</h2>
                    <div v-html="toParagraphs(product.description)" />
                </VCol>
            </VRow>
            <VRow v-if="product.illustrations.length > 0">
                <VCol>
                    <h2>Illustrations</h2>

                    <VRow>
                        <VCol
                            v-for="(illustration, i) in product.illustrations"
                            :key="i"
                            cols="12"
                            md="4"
                        >
                            <VCard
                                variant="flat"
                                class="pa-0"
                            >
                                <VCardText class="image-container">
                                    <img
                                        :src="illustration.path"
                                        :alt="product.name"
                                    >
                                </VCardText>
                            </VCard>
                        </VCol>
                    </VRow>
                </VCol>
            </VRow>
        </VContainer>
    </BedeBlock>
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
