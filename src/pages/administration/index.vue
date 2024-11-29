<script setup lang="ts">
import { useHead } from '@vueuse/head'

useHead({
    title: 'Administration',
})

/* const { products, getProducts, storeProducts } = useProducts()

const router = useRouter()

async function testStoreProducts() {
    await storeProducts({
        name: 'Test',
        quickDescription: 'short comment',
        description: 'long comment',
        price: 10.50,
        images: ['test'],
    })
} */
</script>

<template>
    <h1>Admin Dashboard</h1>
    <h2>Ici faut imaginer des petites stats bien stylées et tout</h2>
    <!-- <VBtn @click="logout">
            Logout
        </VBtn>

        <VBtn @click="getProducts">
            Get Products
        </VBtn>

        <VBtn @click="testStoreProducts">
            Store Product
        </VBtn>

        <div>
            {{ products }}
        </div> -->
</template>
