<script setup lang="ts">
import type { DataTableHeader } from '@/types'
import type { SchemaType } from '@root/amplify/data/resource'
import CreateArticleDialog from '@/components/admin/articles/CreateArticleDialog.vue'
import DeleteArticleDialog from '@/components/admin/articles/DeleteArticleDialog.vue'
import EditArticleDialog from '@/components/admin/articles/EditArticleDialog.vue'
import useDayjs from '@/composables/dayjs'
import useNumbers from '@/composables/numbers'
import useToast from '@/composables/toast'
import useProductsStore from '@/stores/productsStore'
import { useHead } from '@vueuse/head'
import { storeToRefs } from 'pinia'
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'

useHead({
    title: 'Articles',
})

type Product = SchemaType<'Product'> & { id: string }

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
        key: 'createdAt',
        sortable: true,
    },
    {
        title: 'Date de modification',
        key: 'updatedAt',
        sortable: true,
    },
    {
        title: '',
        key: 'actions',
        sortable: false,
        align: 'end',
    },
]

const { products, productsLoading } = storeToRefs(useProductsStore())
const { getProducts } = useProductsStore()
const router = useRouter()
const { dayjs } = useDayjs()
const { formatPrice } = useNumbers()
const { showSuccess } = useToast()

const selectedProduct = ref<Product>()
const displayCreateDialog = ref(false)
const displayEditDialog = ref(false)
const displayDeleteDialog = ref(false)

function showProduct(item: Product) {
    router.push(`/administration/articles/${item.id}`)
}

function handleEditProduct(item: Product) {
    selectedProduct.value = item
    displayEditDialog.value = true
}

function handleDeleteProduct(item: Product) {
    selectedProduct.value = item
    displayDeleteDialog.value = true
}

function handleSuccess(action: 'edit' | 'delete') {
    /* if (action === 'edit') {
        showSuccess('L\'article a été modifié avec succès.')
    } */

    showSuccess(
        action === 'edit'
            ? 'L\'article a été modifié avec succès.'
            : 'L\'article a été supprimé avec succès.',
    )

    getProducts()
}

onMounted(getProducts)
</script>

<template>
    <h1 class="mb-2">
        <VIcon icon="mdi-package-variant" />
        Articles
    </h1>
    <VDataTable
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
    </VDataTable>
    <CreateArticleDialog
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
    />
</template>
