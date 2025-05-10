<script setup lang="ts">
import type { Comic, DataTableHeader } from '@/types'
import useToast from '@/Composables/toast'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { router } from '@inertiajs/vue3'
import { useHead } from '@vueuse/head'
import { ref, watch } from 'vue'
import { route } from 'ziggy-js'

defineOptions({ layout: AdminLayout })

const props = defineProps<{
    comics: Comic[]
    flash: {
        success: string | null
    }
}>()

const { showSuccess } = useToast()

const showPreview = ref(false)
const preview = ref<string>()

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
        key: 'pages_count',
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

function displayPreview(image: string) {
    showPreview.value = true
    preview.value = image
}

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
                <template #item.preview="{ item }">
                    <VImg
                        :src="item.preview"
                        :width="50"
                        :height="50"
                        rounded="lg"
                        class="my-2 cursor-pointer"
                        @click="displayPreview(item.preview)"
                    />
                </template>
                <template #item.actions="{ item }">
                    <VBtn
                        variant="outlined"
                        color="primary"
                        icon="mdi-pencil"
                        class="mr-2"
                        size="small"
                        @click="router.visit(route('admin.comics.edit', item.slug))"
                    />
                    <VBtn
                        variant="outlined"
                        color="error"
                        icon="mdi-trash-can-outline"
                        size="small"
                    />
                    <!-- @click="router.visit(route('admin.comics.destroy', item.slug))" -->
                </template>
            </VDataTable>
        </template>
    </VCard>
    <VDialog
        v-model="showPreview"
        width="500"
    >
        <VImg
            :src="preview"
        />
    </VDialog>
</template>
