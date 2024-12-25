<script setup lang="ts">
import type { AdminProduct, AdminProductList, DataTableHeader } from '@/types'
import CreateArticleDialog from '@/Components/Admin/Articles/CreateArticleDialog.vue'
import DeleteArticleDialog from '@/Components/Admin/Articles/DeleteArticleDialog.vue'
import EditArticleDialog from '@/Components/Admin/Articles/EditArticleDialog.vue'
import useNumbers from '@/Composables/numbers'
import useToast from '@/Composables/toast'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { router } from '@inertiajs/vue3'
import { useHead } from '@vueuse/head'
import { ref } from 'vue'
import { route } from 'ziggy-js'

interface Props {
    products: AdminProductList[]
}

defineOptions({ layout: AdminLayout })

defineProps<Props>()

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
        title: 'Poids',
        key: 'weight',
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

const { formatPrice } = useNumbers()
const { showSuccess } = useToast()

const editedProduct = ref<AdminProduct>()
const deletedProduct = ref<AdminProductList>()
const displayCreateDialog = ref(false)
const displayEditDialog = ref(false)
const displayDeleteDialog = ref(false)

function handleSuccess(action: 'edit' | 'delete') {
    showSuccess(
        action === 'edit'
            ? 'L\'article a été modifié avec succès.'
            : 'L\'article a été supprimé avec succès.',
    )

    router.reload()
}

async function handleEditProduct(item: AdminProductList) {
    editedProduct.value = await fetch(route('products.showApi', { product: item.id }))
        .then(response => response.json())
        .then(data => data)

    displayEditDialog.value = true
}

function handleDeleteProduct(item: AdminProductList) {
    deletedProduct.value = item
    displayDeleteDialog.value = true
}

useHead({
    title: 'Articles',
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
        <template #item.price="{ item }">
            {{ formatPrice(item.price) }}
        </template>
        <template #item.weight="{ item }">
            {{ item.weight }} g.
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
    <EditArticleDialog
        v-if="editedProduct"
        v-model="displayEditDialog"
        v-model:product="editedProduct"
        @success="handleSuccess('edit')"
    />
    <DeleteArticleDialog
        v-if="deletedProduct"
        v-model="displayDeleteDialog"
        :product="deletedProduct"
        @success="handleSuccess('delete')"
    />
</template>
