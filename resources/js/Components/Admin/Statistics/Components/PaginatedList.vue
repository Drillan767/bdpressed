<script setup lang="ts" generic="T">
interface Props {
    title: string
    loading: boolean
    prependIcon: string
    items: T[]
    itemsPerPage?: number
}

defineProps<Props>()
</script>

<template>
    <VCard
        :title
        :loading
        :prepend-icon
    >
        <template #text>
            <VList>
                <VDataIterator
                    :items="items"
                    :items-per-page="itemsPerPage"
                >
                    <template #default="{ items }">
                        <template
                            v-for="{ raw: item } in items"
                            :key="item.id || item"
                        >
                            <slot
                                name="item"
                                :item="item"
                            />
                        </template>
                    </template>
                    <template #no-data>
                        <p class="text-center">
                            Rien à afficher
                        </p>
                    </template>
                    <template #footer="{ page, pageCount, prevPage, nextPage }">
                        <div class="d-flex align-center justify-center pa-4">
                            <VBtn
                                :disabled="page === 1"
                                icon="mdi-chevron-left"
                                density="comfortable"
                                variant="tonal"
                                rounded
                                @click="prevPage"
                            />
                            <div class="mx-2 text-caption">
                                Page {{ page }} sur {{ pageCount }}
                            </div>
                            <VBtn
                                :disabled="page >= pageCount"
                                icon="mdi-chevron-right"
                                density="comfortable"
                                variant="tonal"
                                rounded
                                @click="nextPage"
                            />
                        </div>
                    </template>
                </VDataIterator>
            </VList>
        </template>
    </VCard>
</template>
