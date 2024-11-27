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
            class="navigation rounded-b-xl pr-8"
            elevation="4"
        >
            <template #prepend>
                <RouterLink to="/">
                    <VAvatar
                        size="96"
                        class="mt-16 ml-16 elevation-4"
                        image="/logo.png"
                    />
                </RouterLink>
            </template>
            <template #default>
                <VContainer>
                    <VRow>
                        <VCol class="links d-flex justify-center ga-4">
                            <RouterLink to="/">
                                Accueil
                            </RouterLink>
                            <RouterLink to="/">
                                Bédés
                            </RouterLink>
                            <RouterLink to="/">
                                Boutique
                            </RouterLink>
                            <RouterLink to="/">
                                Contact
                            </RouterLink>
                        </VCol>
                    </VRow>
                </VContainer>
            </template>
            <template #append>
                <div class="mt-16 d-flex ga-2">
                    <RouterLink to="/connexion">
                        <VAvatar
                            size="48"
                            image="/logo.png"
                            class="elevation-4"
                        />
                    </RouterLink>

                    <VBadge
                        color="primary"
                        content="45"
                        max="9"
                    >
                        <VAvatar
                            size="48"
                            image="/logo.png"
                            class="elevation-4 cursor-pointer"
                            @click="drawer = true"
                        />
                    </VBadge>
                </div>

                <!-- <VBtn
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
                </VBtn> -->
            </template>
        </VAppBar>
        <VMain class="h-screen">
            <VContainer class="mt-16">
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
    .v-toolbar__content {
        overflow: initial;
    }

    .v-toolbar-title__placeholder {
        font-size: 28px;
    }

    .links a {
        text-decoration: none;
        font-size: 24px;
    }
}

.placeholder {
    font-size: 35px;
}
</style>
