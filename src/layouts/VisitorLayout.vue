<script setup lang="ts">
import { ref } from 'vue'

const drawer = ref(false)
</script>

<template>
    <VNavigationDrawer
        v-model="drawer"
        :temporary="true"
        location="right"
    >
        <VListItem
            title="Panier"
            color="primary"
            class="bg-primary"
        >
            <template #append>
                <VBtn
                    icon="mdi-close"
                    variant="text"
                    color="white"
                    @click="drawer = false"
                />
            </template>
        </VListItem>
    </VNavigationDrawer>
    <VAppBar title="Bjr" border>
        <template #append>
            <VBtn
                icon="mdi-account-circle-outline"
                variant="text"
            />
            <VBtn stacked>
                <VBadge
                    color="primary"
                    content="45"
                    max="9"
                    @click="drawer = true"
                >
                    <v-icon icon="mdi-shopping-outline" />
                </VBadge>

                Panier
            </VBtn>
        </template>
    </VAppBar>
    <VMain class="visitors-layout h-screen">
        <VContainer style="position: relative; z-index: 1;">
            <slot />
        </VContainer>
    </VMain>
</template>

<style lang="scss" scoped>
.visitors-layout {
    background-image: url('/bg.jpg');
    background-size: cover;
    background-position: center;

    &::before {
        content: '';
        position: absolute;
        z-index: 0;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: url('/flowers.png');
        background-size: 600px 300px;
        background-repeat: repeat
    }
}
</style>
