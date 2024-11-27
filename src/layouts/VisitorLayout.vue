<script setup lang="ts">
import { ref } from 'vue'

const drawer = ref(false)
const linksDrawer = ref(false)
</script>

<template>
    <VLayout class="visitors-layout">
        <VNavigationDrawer v-model="linksDrawer">
            <VListItem
                prepend-icon="mdi-home"
                title="Accueil"
                nav
                exact
            />
            <VListItem
                prepend-icon="mdi-package-variant"
                title="Bédés"
                nav
            />
            <VListItem
                prepend-icon="mdi-store"
                title="Boutique"
                nav
            />
            <VListItem
                prepend-icon="mdi-email-fast"
                title="Contact"
                nav
            />
        </VNavigationDrawer>
        <VAppBar
            class="navigation rounded-b-xl pr-8"
            elevation="4"
        >
            <template #prepend>
                <VAppBarNavIcon
                    class="display-sm-and-up"
                    @click="linksDrawer = true"
                />
                <RouterLink
                    class="mt-16 ml-md-16"
                    to="/"
                >
                    <VAvatar
                        size="96"
                        class=" elevation-4"
                        image="/logo.png"
                    />
                </RouterLink>
            </template>
            <template #default>
                <VContainer class="hidden-sm-and-down">
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
            </template>
        </VAppBar>
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
