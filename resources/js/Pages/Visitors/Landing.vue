<script setup lang="ts">
import type { Comic } from '@/types'
import ComicDetails from '@/Components/ComicDetails.vue'
import DescriptionBlock from '@/Components/DescriptionBlock.vue'
import VisitorsLayout from '@/Layouts/VisitorsLayout.vue'
import { useHead } from '@vueuse/head'
import { ref } from 'vue'

interface Props {
    description_url: string
    description_text: string
    comics: {
        id: number
        title: string
        preview: string
    }[]
}

defineOptions({ layout: VisitorsLayout })

defineProps<Props>()

useHead({
    title: 'Accueil',
})

const showComicDetails = ref(false)
const comicDetail = ref<Comic>()

async function comicDetails(id: number) {
    comicDetail.value = await fetch(route('comic.details', { comic: id }))
        .then(response => response.json())
        .then(data => data)

    showComicDetails.value = true
}
</script>

<template>
    <VContainer>
        <VRow class="mb-8">
            <VCol class="d-flex justify-center">
                <DescriptionBlock
                    :image="description_url"
                    :message="description_text"
                />
            </VCol>
        </VRow>
        <VRow>
            <VCol>
                <VCard class="bede-block">
                    <VCardText class="bede-text">
                        <h1 class="mb-4">
                            <VIcon icon="mdi-grid-large" />
                            Bédés
                        </h1>
                        <VContainer>
                            <VRow>
                                <VCol
                                    v-for="comic in comics"
                                    :key="comic.id"
                                    cols="12"
                                    sm="6"
                                    md="4"
                                    lg="3"
                                >
                                    <VCard @click="comicDetails(comic.id)">
                                        <VImg
                                            :src="comic.preview"
                                            :alt="comic.title"
                                            class="w-100"
                                        />
                                        <h3 class="text-h6 pa-2 mb-0 comic-title">
                                            {{ comic.title }}
                                        </h3>
                                    </VCard>
                                </VCol>
                            </VRow>
                        </VContainer>
                    </VCardText>
                </VCard>
            </VCol>
        </VRow>
        <ComicDetails
            v-if="comicDetail"
            v-model="showComicDetails"
            :comic="comicDetail"
        />
    </VContainer>
</template>
