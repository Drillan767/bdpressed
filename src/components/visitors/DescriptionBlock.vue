<script setup lang="ts">

</script>

<template>
    <VCard
        class="description-block"
        width="650"
        height="350"
    >
        <VCardText>
            <VContainer>
                <VRow>
                    <VCol class="">
                        <VImg
                            class="rounded-xl"
                            src="/yell.png"
                            :aspect-ratio="1"
                            max-width="250"
                            max-height="250"
                            cover
                        />
                    </VCol>
                    <VCol class="text-center d-flex flex-column justify-center text-secondary">
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
    box-shadow: 1px 16px 18px 7px #891e00;
    border-radius: 48px !important;
}
</style>
