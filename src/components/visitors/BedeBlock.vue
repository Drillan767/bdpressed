<template>
    <VCard
        class="bede-block"
    >
        <VCardText class="bede-text">
            <slot />
        </VCardText>
    </VCard>
</template>

<style lang="scss" scoped>
.bede-block {
    border-radius: 48px !important;
    box-shadow: 0 16px 18px 0 #891e00;

    .bede-text {
        padding: 3rem;
        color: rgb(var(--v-theme-secondary));
    }
}
</style>
