<script setup lang="ts">
import useStrings from '@/Composables/strings'
import { computed } from 'vue'
import { useDisplay } from 'vuetify'

interface Props {
    message: string
    image: string
}

const props = defineProps<Props>()

const { smAndUp } = useDisplay()
const { toParagraphs } = useStrings()

const paragraphs = computed(() => toParagraphs(props.message))
</script>

<template>
    <VCard
        class="description-block"
        width="650"
        :height="smAndUp ? 350 : 'auto'"
    >
        <VCardText>
            <VContainer>
                <VRow>
                    <VCol cols="12" md="6">
                        <VImg
                            class="rounded-xl"
                            :src="image"
                            :aspect-ratio="1"
                            max-width="250"
                            max-height="250"
                            cover
                        />
                    </VCol>
                    <VCol
                        cols="12"
                        md="6"
                        class="text-center d-flex flex-column justify-center text-secondary"
                    >
                        <div v-html="paragraphs" />
                    </VCol>
                </VRow>
            </VContainer>
        </VCardText>
    </VCard>
</template>

<style lang="scss" scoped>
.description-block {
    padding: 1rem;
    border-radius: 1rem;
    box-shadow: 0 16px 18px 0 #891e00;
    border-radius: 48px !important;
}
</style>
