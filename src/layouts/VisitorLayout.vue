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
                class="basket-title bg-primary"
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

            <p class="placeholder">
                Lorsque toudincou, un panier vide. 👁️👄👁️
            </p>
        </VNavigationDrawer>
        <VAppBar

            title="Bédéprimée 🗣️🗣️🔊🔊"
            border
        >
            <template #prepend>
                <VAvatar
                    class="ml-16"
                    image="/logo.png"
                />
            </template>
            <template #append>
                <VBtn
                    stacked
                    to="/connexion"
                >
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
    </VLayout>
</template>

<style lang="scss" scoped>
.basket-title :deep(.v-list-item-title) {
    font-size: 40px;
}

:deep(.v-toolbar) {
    .v-toolbar-title__placeholder {
        font-size: 28px;
    }
}

.placeholder {
    font-size: 35px;
}
</style>
