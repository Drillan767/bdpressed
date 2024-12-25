<script setup lang="ts">
import type { DataTableHeader, AdminProductList } from '@/types'
import { ref } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import CreateArticleDialog from '@/Components/Admin/Articles/CreateArticleDialog.vue'
import { useHead } from '@vueuse/head'

interface Props {
    products: AdminProductList[]
}

const headers: DataTableHeader[] = [
    {
        title: 'Nom',
        key: 'name',
        sortable: true,
    },
    {
        title: 'Prix',
        key: 'price',
        sortable: true,
    },
    {
        title: 'Description',
        key: 'quickDescription',
        sortable: true,
    },
    {
        title: 'Date d\'ajout',
        key: 'created_at',
        sortable: true,
    },
    {
        title: 'Date de modification',
        key: 'updated_at',
        sortable: true,
    },
    {
        title: '',
        key: 'actions',
        sortable: false,
        align: 'end',
    },
]

const props = defineProps<Props>()

const displayCreateDialog = ref(false)

defineOptions({ layout: AdminLayout })
useHead({
    title: 'Articles'
})
</script>

<template>
    <h1 class="mb-2">
        <VIcon icon="mdi-package-variant" />
        Articles
    </h1>
    <VDataTable
        :headers
        :items="products"
    >
        <template #top>
            <div class="d-flex justify-end mt-4 mr-4">
                <VBtn
                    variant="outlined"
                    color="primary"
                    append-icon="mdi-package-variant-plus"
                    @click="displayCreateDialog = true"
                >
                    Créer un article
                </VBtn>
            </div>
        </template>
        <template #item.actions="{ item }">
            <div class="d-flex justify-end">
                <VBtn
                    variant="text"
                    color="blue"
                    icon="mdi-eye"
                    @click="router.visit(route('products.show', { slug: item.slug }))"
                />

                <VBtn
                    variant="text"
                    color="primary"
                    icon="mdi-pencil"
                    class="mx-2"
                    @click="handleEditProduct(item)"
                />

                <VBtn
                    variant="text"
                    color="error"
                    icon="mdi-delete"
                    @click="handleDeleteProduct(item)"
                />
            </div>
        </template>
    </VDataTable>
    <CreateArticleDialog
        v-model="displayCreateDialog"
        @success="router.reload()"
    />
<!--    <VDataTable
        :headers
        :items="products"
        :loading="productsLoading ? 'primary' : false"
    >
        <template #top>
            <div class="d-flex justify-end mt-4 mr-4">
                <VBtn
                    variant="outlined"
                    color="primary"
                    append-icon="mdi-package-variant-plus"
                    @click="displayCreateDialog = true"
                >
                    Créer un article
                </VBtn>
            </div>
        </template>
        <template #item.createdAt="{ item }">
            {{ dayjs(item.createdAt).format('DD/MM/YYYY HH:mm') }}
        </template>
        <template #item.updatedAt="{ item }">
            {{ dayjs(item.updatedAt).format('DD/MM/YYYY HH:mm') }}
        </template>
        <template #item.price="{ item }">
            {{ formatPrice(item.price) }}
        </template>
        <template #item.actions="{ item }">
            <div class="d-flex justify-end">
                <VBtn
                    variant="text"
                    color="blue"
                    icon="mdi-eye"
                    @click="showProduct(item)"
                />

                <VBtn
                    variant="text"
                    color="primary"
                    icon="mdi-pencil"
                    class="mx-2"
                    @click="handleEditProduct(item)"
                />

                <VBtn
                    variant="text"
                    color="error"
                    icon="mdi-delete"
                    @click="handleDeleteProduct(item)"
                />
            </div>
        </template>
    </VDataTable>-->
<!--    <CreateArticleDialog
        v-model="displayCreateDialog"
        @success="getProducts"
    />
    <EditArticleDialog
        v-if="selectedProduct"
        v-model="displayEditDialog"
        v-model:product="selectedProduct"
        @success="handleSuccess('edit')"
    />
    <DeleteArticleDialog
        v-if="selectedProduct"
        v-model="displayDeleteDialog"
        :product="selectedProduct"
        @success="handleSuccess('delete')"
    />-->
</template>
