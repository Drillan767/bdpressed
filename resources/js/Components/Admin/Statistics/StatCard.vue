<script setup lang="ts">
interface Props {
    title: string
    subtitle: string
    color: string
    icon: string
}

defineProps<Props>()
</script>

<template>
    <VCard
        :color
        class="stat-card"
        rounded="xl"
    >
        <VCardSubtitle class="stat-card-subtitle">
            {{ subtitle }}
        </VCardSubtitle>
        <VCardTitle>
            {{ title }}
        </VCardTitle>
        <VIcon
            :icon
            class="stat-icon"
            color="white"
        />
    </VCard>
</template>

<style scoped>
.stat-card {
    position: relative;
    padding: 1rem;
}

.stat-icon {
    position: absolute;
    right: 0;
    top: 1.75rem;
    opacity: 0.6;
    font-size: 8rem;
}

.stat-card-subtitle {
    line-height: 2.2 !important;
}
</style>
