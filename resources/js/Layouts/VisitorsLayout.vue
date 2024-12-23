<script setup lang="ts">
// import Cart from '@/components/shop/Cart.vue'
// import useCartStore from '@/stores/cartStore'
import { useHead } from '@vueuse/head'
import { storeToRefs } from 'pinia'
import { Link } from '@inertiajs/vue3'
import { provide, ref, watch } from 'vue'
import { useDisplay } from 'vuetify'

const links = [
    { title: 'Accueil', to: '/' },
    { title: 'Boutique', to: '/boutique' },
    { title: 'Contact', to: '/contact' },
]

const icons = [
    'mdi-facebook',
    'mdi-twitter',
    'mdi-linkedin',
    'mdi-instagram',
]

useHead({
    titleTemplate: () => `%s | Bédéprimée`,
})

const { smAndDown } = useDisplay()
// const { cart } = storeToRefs(useCartStore())

const drawer = ref(false)
const linksDrawer = ref(false)

const openDrawer = () => drawer.value = true

watch(smAndDown, (val) => {
    linksDrawer.value = val
})

watch(drawer, (val) => {
    const html = document.querySelector('html')
    if (val) {
        html?.classList.add('overflow-hidden')
    }
    else {
        html?.classList.remove('overflow-hidden')
    }
})

provide('openDrawer', openDrawer)
</script>

<template>
    <VLayout class="visitors-layout">
        <VNavigationDrawer
            v-model="linksDrawer"
            :mobile="smAndDown"
            :persistent="false"
            :temporary="true"
        >
            <VList>
                <VListItem
                    prepend-icon="mdi-home"
                    title="Accueil"
                    density="comfortable"
                    to="/"
                    exact
                />
                <VListItem
                    prepend-icon="mdi-store"
                    title="Boutique"
                    to="/boutique"
                    exact
                />
                <VListItem
                    prepend-icon="mdi-email-fast"
                    title="Contact"
                    to="/contact"
                    exact
                />
            </VList>
        </VNavigationDrawer>
<!--        <Cart v-model="drawer" />-->
        <VAppBar
            class="navigation rounded-b-xl pr-8"
            elevation="4"
        >
            <template #prepend>
                <VAppBarNavIcon
                    class="hidden-sm-and-up"
                    @click="linksDrawer = true"
                />
                <Link
                    href="/"
                    class="mt-16 ml-md-16"
                >
                    <VAvatar
                        size="96"
                        class=" elevation-4"
                        image="/assets/images/logo.png"
                    />
                </Link>

            </template>
            <template #default>
                <VContainer class="hidden-sm-and-down">
                    <VRow>
                        <VCol class="links d-flex justify-center ga-4">
                            <Link
                                :class="{ 'active': $page.url === '/' }"
                                href="/"
                            >
                                Accueil
                            </Link>
                            <Link href="/boutique">
                                Boutique
                            </Link>
                            <Link href="/contact">
                                Contact
                            </Link>
                        </VCol>
                    </VRow>
                </VContainer>
            </template>
            <template #append>
                <div class="mt-16 mr-8 mr-md-16 d-flex ga-2">
                    <VTooltip location="bottom">
                        <template #activator="{ props }">
                            <Link
                                v-bind="props"
                                href="/connexion"
                            >
                                <VAvatar
                                    size="48"
                                    image="/assets/images/account.png"
                                    class="elevation-4"
                                />
                            </Link>
                        </template>

                        Accéder à votre compte
                    </VTooltip>

                    <VTooltip location="bottom">
                        <template #activator="{ props }">
<!--                            <VBadge
                                v-bind="props"
                                :model-value="cart.length > 0"
                                :content="cart.length"
                                color="primary"
                                max="9"
                            >

                            </VBadge>-->
                            <VAvatar
                                size="48"
                                image="/assets/images/cart.png"
                                class="elevation-4 cursor-pointer"
                                @click="drawer = true"
                            />
                        </template>
                        Panier
                    </VTooltip>
                </div>
            </template>
        </VAppBar>

        <VMain min-height="100vh">
            <VContainer
                class="mt-16"
                min-height="100vh"
            >
                <VRow>
                    <VCol>
                        <slot />
                    </VCol>
                </VRow>
            </VContainer>
            <VFooter>
                <VContainer>
                    <VRow justify="center" no-gutters>
                        <VBtn
                            v-for="link in links"
                            :key="link.title"
                            :to="link.to"
                            variant="text"
                            color="secondary"
                            class="mx-2"
                            exact
                        >
                            {{ link.title }}
                        </VBtn>
                    </VRow>
                    <VDivider class="my-4" />
                    <VRow justify="center" no-gutters>
                        <VIcon
                            v-for="icon in icons"
                            :key="icon"
                            :icon="icon"
                            class="mx-2"
                        />
                    </VRow>
                </VContainer>
            </VFooter>
        </VMain>
    </VLayout>
</template>

<style lang="scss" scoped>
:deep(.v-navigation-drawer__scrim) {
    position: fixed;
}

:deep(.v-toolbar) {
    .v-toolbar__content {
        overflow: initial;
    }

    .links a {
        text-decoration: none;
        color: rgb(var(--v-theme-secondary));

        &.active {
            color: rgb(var(--v-theme-primary));
        }

    }
}
</style>
