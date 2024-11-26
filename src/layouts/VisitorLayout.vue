<script setup lang="ts">
import { ref } from 'vue'

const drawer = ref(false)
</script>

<template>
    <VLayout class="visitors-layout">
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
        <VAppBar
            title="Bjr"
            border
        >
            <template #append>
                <VBtn stacked>
                    <VIcon icon="mdi-account-circle-outline" />
                    Compte
                </VBtn>
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
        <VMain class="h-screen">
            <VContainer>
                <VRow>
                    <VCol>
                        <slot />
                    </VCol>
                </VRow>
            </VContainer>
        </VMain>
        <img src="/bg.jpg" alt="bg" style="position: fixed; inset: 0;">
    </VLayout>
</template>
