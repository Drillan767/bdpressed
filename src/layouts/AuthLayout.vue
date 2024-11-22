<script setup lang="ts">

</script>

<template>
    <VMain class="h-screen">
        <VCard
            class="h-100"
            image="https://cdn.vuetifyjs.com/images/cards/sunshine.jpg"
            flat
            tile
        >
            <div class="h-100 d-flex justify-center align-center">
                <slot />
            </div>
        </VCard>
    </VMain>
</template>

<style>
html { overflow-y: auto; }
</style>
