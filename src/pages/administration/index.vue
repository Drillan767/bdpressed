<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue'

/* const { products, getProducts, storeProducts } = useProducts()

definePage({
    meta: {
        title: 'Administration',
        requiresAuth: true,
        requiresRole: 'admin',
    },
})

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
    <AdminLayout>
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
    </AdminLayout>
</template>
