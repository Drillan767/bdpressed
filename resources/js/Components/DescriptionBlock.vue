<script setup lang="ts">
import { useDisplay } from 'vuetify'

const { smAndUp } = useDisplay()
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
                            src="/assets/images/yell.png"
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
                        <p>
                            Bienvenue dans mon journal intime pas très intime, mi burn-out mi révolution pantoufle.
                        </p>
                        <p>
                            Ici règne le désordre mental et l'auto sabotage glorieux ¥
                        </p>
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
