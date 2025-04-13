<script setup lang="ts">
import ComicForm from './ComicForm.vue'

const displayDialog = defineModel<boolean>({ required: true })
</script>

<template>
    <VDialog
        v-model="displayDialog"
        max-width="960"
        persistent
        scrollable
    >
        <VCard
            title="Créer une BD"
            prepend-icon="mdi-draw"
        >
            <template #text>
                <ComicForm />
            </template>
            <template #actions>
                <VBtn
                    variant="text"
                    @click="displayDialog = false"
                >
                    Annuler
                </VBtn>
                <VBtn
                    variant="flat"
                    color="primary"
                >
                    Créer
                </VBtn>
            </template>
        </VCard>
    </VDialog>
</template>
