<script setup lang="ts">
import type { Comic, DataTableHeader } from '@/types'
import CreateComicDialog from '@/Components/Admin/Comics/CreateComicDialog.vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useHead } from '@vueuse/head'
import { ref } from 'vue'

defineOptions({ layout: AdminLayout })

defineProps<{
    comics: Comic[]
}>()

const displayCreateDialog = ref(false)

const headers: DataTableHeader[] = [
    {
        title: 'Titre',
        key: 'title',
    },
    {
        title: 'Aperçu',
        key: 'preview',
    },
    {
        title: 'Nombre de pages',
        key: 'pages',
    },
    {
        title: 'Date de création',
        key: 'created_at',
    },
    {
        title: 'Actions',
        key: 'actions',
        sortable: false,
        align: 'end',
    },
]

useHead({
    title: 'Bédés',
})
</script>

<template>
    <h1 class="mb-4">
        <VIcon icon="mdi-draw" />
        Bédés
    </h1>
    <VCard>
        <template #text>
            <VDataTable
                :headers="headers"
                :items="comics"
            >
                <template #top>
                    <div class="d-flex justify-end mt-4 mr-4">
                        <VBtn
                            variant="outlined"
                            color="primary"
                            @click="displayCreateDialog = true"
                        >
                            Créer une BD
                        </VBtn>
                    </div>
                </template>
            </VDataTable>
        </template>
    </VCard>
    <CreateComicDialog
        v-model="displayCreateDialog"
    />
</template>
