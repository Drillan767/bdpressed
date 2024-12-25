<script setup lang="ts">
import type { Catalog } from '@/types'
import useNumbers from '@/Composables/numbers'
import VisitorsLayout from '@/Layouts/VisitorsLayout.vue'
import useCartStore from '@/Stores/cartStore'
import { useHead } from '@vueuse/head'
import { inject, onMounted, ref } from 'vue'
import { useDisplay } from 'vuetify'

defineOptions({ layout: VisitorsLayout })
const { products } = defineProps<{
    products: Catalog[]
}>()

useHead({
    title: 'Boutique',
})

const { formatPrice } = useNumbers()
const { smAndDown } = useDisplay()
const { addItem } = useCartStore()

const openDrawer = inject<() => void>('openDrawer')

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
</script>

<template>
    <VContainer>
        <VRow>
            <VCol>
                <VCard class="bede-block">
                    <VCardText class="bede-text">
                        <h1 class="text-center text-h4 mb-4 bd">
                            Mes petites créations à vendre :
                        </h1>
                        <h2 class="text-center my-12 bd">
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
                                        href="/boutique/illustration"
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
                                        :href="`/boutique/${product.slug}`"
                                        variant="flat"
                                    >
                                        <VCardText>
                                            <VContainer class="pa-0 text-secondary">
                                                <VRow no-gutters>
                                                    <VCol
                                                        :style="`height: ${smAndDown ? '200px' : '500px'}`"
                                                        class="image-container"
                                                    >
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
                                                            @click.prevent="handleAddToCart(product)"
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
                                        </VCardText>
                                    </VCard>
                                </VCol>
                            </VRow>
                        </VContainer>
                    </VCardText>
                </VCard>
            </VCol>
        </VRow>
    </VContainer>
</template>
