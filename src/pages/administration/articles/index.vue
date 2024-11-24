<script setup lang="ts">
import type { SchemaType } from '@root/amplify/data/resource'
import CreateArticleDialog from '@/components/admin/articles/CreateArticleDialog.vue'
import useDayjs from '@/composables/dayjs'
import useNumbers from '@/composables/numbers'
import AdminLayout from '@/layouts/AdminLayout.vue'
import useProductsStore from '@/stores/productsStore'
import { storeToRefs } from 'pinia'
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'

type Product = SchemaType<'Product'>

interface DataTableHeader {
    title: string
    align?: 'start' | 'center' | 'end'
    sortable?: boolean
    width?: string | number
    key: string
    nowrap?: boolean
    cellProps?: {
        class: string
    }
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

const displayCreateDialog = ref(false)

function showProduct(item: Product) {
    router.push(`/administration/articles/${item.id}`)
}

onMounted(getProducts)
</script>

<template>
    <AdminLayout>
        <h1>Articles</h1>
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
                    />

                    <VBtn
                        variant="text"
                        color="error"
                        icon="mdi-delete"
                    />
                </div>
            </template>
        </VDataTable>
        <CreateArticleDialog
            v-model="displayCreateDialog"
            @success="getProducts"
        />
    </AdminLayout>
</template>
