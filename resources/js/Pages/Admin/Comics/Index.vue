<script setup lang="ts">
import type { Comic, DataTableHeader } from '@/types'
import useToast from '@/Composables/toast'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { router } from '@inertiajs/vue3'
import { useHead } from '@vueuse/head'
import { watch } from 'vue'
import { route } from 'ziggy-js'

defineOptions({ layout: AdminLayout })

const props = defineProps<{
    comics: Comic[]
    flash: {
        success: string | null
    }
}>()

const { showSuccess } = useToast()

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

watch(() => props.flash.success, (value) => {
    if (value)
        showSuccess(value)
}, { immediate: true })
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
                            append-icon="mdi-plus"
                            @click="router.visit(route('comics.create'))"
                        >
                            Créer une BD
                        </VBtn>
                    </div>
                </template>
            </VDataTable>
        </template>
    </VCard>
</template>
