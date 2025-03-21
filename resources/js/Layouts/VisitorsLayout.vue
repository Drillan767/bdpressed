<script setup lang="ts">
import Cart from '@/Components/Shop/Cart.vue'
import useToast from '@/Composables/toast'
import useCartStore from '@/Stores/cartStore'
import { Link, router } from '@inertiajs/vue3'
import { useHead } from '@vueuse/head'
import { storeToRefs } from 'pinia'
import { computed, provide, ref, watch } from 'vue'
import { useDisplay } from 'vuetify'
import { route } from 'ziggy-js'

const links = [
    { title: 'Accueil', href: '/' },
    { title: 'Boutique', href: '/boutique' },
    { title: 'Contact', href: '/contact' },
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
const { cart } = storeToRefs(useCartStore())
const { showError } = useToast()

const drawer = ref(false)
const linksDrawer = ref(false)

const params = computed(() => Object.fromEntries(new URLSearchParams(location.search)))

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

watch(params, (value) => {
    if ('error' in value) {
        switch (value.error) {
            case 'empty-cart':
                showError('Vous devez avoir au moins un article dans le panier')
                break
        }
    }
}, { immediate: true })

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
                    href="/"
                    exact
                />
                <VListItem
                    prepend-icon="mdi-store"
                    title="Boutique"
                    href="/boutique"
                    exact
                />
                <VListItem
                    prepend-icon="mdi-email-fast"
                    title="Contact"
                    href="/contact"
                    exact
                />
            </VList>
        </VNavigationDrawer>
        <Cart v-model="drawer" />
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
                                :class="{ active: $page.url === '/' }"
                                href="/"
                            >
                                Accueil
                            </Link>
                            <Link
                                :class="{ active: $page.url.startsWith('/boutique') }"
                                :href="route('shop.index')"
                            >
                                Boutique
                            </Link>
                            <Link
                                :class="{ active: $page.url === '/contact' }"
                                :href="route('contact')"
                            >
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
                                :href="route('login')"
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
                            <VBadge
                                v-bind="props"
                                :model-value="cart.length > 0"
                                :content="cart.length"
                                color="primary"
                                max="9"
                            >
                                <VAvatar
                                    size="48"
                                    image="/assets/images/cart.png"
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
                            variant="text"
                            color="secondary"
                            class="mx-2"
                            exact
                            @click="router.visit(link.href)"
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
