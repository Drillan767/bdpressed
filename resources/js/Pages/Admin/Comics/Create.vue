<script setup lang="ts">
import ComicForm from '@/Components/Admin/Comics/ComicForm.vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineOptions({ layout: AdminLayout })
</script>

<template>
    <h1 class="mb-4">
        <VIcon icon="mdi-draw" />
        Nouvelle bédé
    </h1>

    <VContainer>
        <VRow>
            <VCol>
                <VCard>
                    <ComicForm />
                </VCard>
            </VCol>
        </VRow>
    </VContainer>
</template>
