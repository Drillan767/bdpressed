<script setup lang="ts">
import type { Comic } from '@/types'
import useStrings from '@/Composables/strings'
import { ref, watch } from 'vue'

defineProps<{
    comic: Comic
}>()

const show = defineModel<boolean>('show')

const { toParagraphs } = useStrings()

const showDescription = ref(false)

watch(show, () => {
    showDescription.value = false
}, { immediate: true })
</script>

<template>
    <VDialog
        v-model="show"
        max-width="800"
    >
        <VCard>
            <VCarousel
                hide-delimiters
                :show-arrows="false"
                progress="secondary"
            >
                <VCarouselItem
                    v-for="page in comic.pages"
                    :key="page.id"
                    :src="page.image"
                />
            </VCarousel>
            <VContainer>
                <VRow
                    no-gutters
                    class="flex-column flex-md-row"
                >
                    <VCol
                        class="flex-md-shrink-0 flex-md-grow-1"
                    >
                        <h3 class="text-h5 font-weight-bold mb-0 comic-title">
                            {{ comic.title }}
                        </h3>
                    </VCol>
                    <VCol
                        class="d-flex align-center flex-md-shrink-1 flex-md-grow-0 mt-2 mt-md-0"
                    >
                        <VBtn
                            :append-icon="showDescription ? 'mdi-chevron-up' : 'mdi-chevron-down'"
                            variant="flat"
                            @click="showDescription = !showDescription"
                        >
                            description
                        </VBtn>
                    </VCol>
                </VRow>
            </VContainer>
            <VExpandTransition>
                <div v-show="showDescription">
                    <VDivider />
                    <VCardText>
                        <div v-html="toParagraphs(comic.description)" />
                    </VCardText>
                </div>
            </VExpandTransition>
        </VCard>
    </VDialog>
</template>

<style lang="scss" scoped>
.comic-title {
    word-wrap: break-word;
    overflow-wrap: break-word;
    hyphens: auto;
    line-height: 1.4;
}
</style>
