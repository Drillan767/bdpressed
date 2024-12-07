<script setup lang="ts">
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
    <div>
        <h1>Boutique</h1>
    </div>
    <div>
        <p>{{ productsLoading ? 'loading...' : product?.name }}</p>
        <p>{{ product }}</p>
    </div>
</template>
