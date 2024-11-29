<script setup lang="ts">
import { useHead } from '@vueuse/head'
import { ref } from 'vue'
import { RouterView } from 'vue-router'

useHead({
    titleTemplate: () => `%s | Bédéprimée`,
})

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
                <VAppBarNavIcon
                    class="hidden-sm-and-up"
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
                            <RouterLink to="/bedes">
                                Bédés
                            </RouterLink>
                            <RouterLink to="/boutique">
                                Boutique
                            </RouterLink>
                            <RouterLink to="/contact">
                                Contact
                            </RouterLink>
                        </VCol>
                    </VRow>
                </VContainer>
            </template>
            <template #append>
                <div class="mt-16 mr-16 d-flex ga-2">
                    <VTooltip location="bottom">
                        <template #activator="{ props }">
                            <RouterLink
                                v-bind="props"
                                to="/connexion"
                            >
                                <VAvatar
                                    size="48"
                                    image="/account.png"
                                    class="elevation-4"
                                />
                            </RouterLink>
                        </template>

                        Accéder à votre compte
                    </VTooltip>

                    <VTooltip location="bottom">
                        <template #activator="{ props }">
                            <VBadge
                                v-bind="props"
                                color="primary"
                                content="45"
                                max="9"
                            >
                                <VAvatar
                                    size="48"
                                    image="/cart.png"
                                    class="elevation-4 cursor-pointer"
                                    @click="drawer = true"
                                />
                            </VBadge>
                        </template>
                        Panier
                    </VTooltip>
                </div>
            </template>
        </VAppBar>

        <VMain class="h-screen">
            <VContainer class="mt-16">
                <VRow>
                    <VCol>
                        <RouterView />
                    </VCol>
                </VRow>
            </VContainer>
        </VMain>
    </VLayout>
</template>

<style lang="scss" scoped>
:deep(.v-toolbar) {
    .v-toolbar__content {
        overflow: initial;
    }

    .links a {
        text-decoration: none;
        color: rgb(var(--v-theme-secondary));

        &.router-link-exact-active {
            color: rgb(var(--v-theme-primary));
        }

    }
}
</style>
