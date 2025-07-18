<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3'
import { useHead } from '@vueuse/head'
import { onMounted, provide, ref } from 'vue'
import { useDisplay } from 'vuetify'
import { route } from 'ziggy-js'

useHead({
    titleTemplate: () => `%s | BédéAdmin ☝️🤓`,
})

const { mobile } = useDisplay()
const page = usePage()

provide('csrfToken', page.props.csrf_token)

const openDrawer = ref(true)
const pendingOrders = ref(0)

onMounted(async () => {
    pendingOrders.value = await fetch(route('orders.pending'))
        .then(response => response.json())
        .then(data => data)
})
</script>

<template>
    <VLayout>
        <VAppBar
            title="Administration"
            border
        >
            <template #prepend>
                <VAppBarNavIcon
                    v-if="mobile"
                    @click="openDrawer = !openDrawer"
                />
            </template>
            <template #append>
                <VMenu>
                    <template #activator="{ props }">
                        <VBtn
                            v-bind="props"
                            color="primary"
                            icon="mdi-account-circle"
                        />
                    </template>
                    <VList>
                        <VListItem
                            prepend-icon="mdi-home"
                            title="Retour au site"
                            @click="router.visit('/')"
                        />
                        <VDivider class="my-2" />
                        <VListItem
                            prepend-icon="mdi-logout"
                            title="Déconnexion"
                            @click="router.post(route('logout'))"
                        />
                    </VList>
                </VMenu>
            </template>
        </VAppBar>
        <VNavigationDrawer
            v-model="openDrawer"
            :permanent="!mobile"
            :temporary="mobile"
        >
            <VList nav>
                <VListItem
                    :active="$page.url.endsWith('/administration')"
                    prepend-icon="mdi-home"
                    title="Accueil"
                    nav
                    @click="router.visit(route('admin.dashboard'))"
                />
                <VDivider class="my-2" />
                <VListItem
                    :active="$page.url.includes('/articles')"
                    prepend-icon="mdi-package-variant"
                    title="Articles"
                    nav
                    @click="router.visit(route('products.index'))"
                />
                <VListItem
                    :active="$page.url.includes('/commandes')"
                    prepend-icon="mdi-package-variant-closed"
                    title="Commandes"
                    nav
                    @click="router.visit(route('orders.index'))"
                >
                    <template #append>
                        <VBadge
                            :model-value="pendingOrders > 0"
                            :content="pendingOrders"
                            color="primary"
                            inline
                        />
                    </template>
                </VListItem>
                <VDivider class="my-2" />
                <VListItem
                    :active="$page.url.includes('/comics')"
                    prepend-icon="mdi-draw"
                    title="Bédés"
                    nav
                    @click="router.visit(route('admin.comics.index'))"
                />
                <VDivider class="my-2" />
                <VListItem
                    :active="$page.url.includes('/parametres')"
                    prepend-icon="mdi-cog-outline"
                    title="Paramètres"
                    nav
                    @click="router.visit(route('settings.website'))"
                />
            </VList>
        </VNavigationDrawer>
        <VMain
            min-height="100vh"
            class="bg-grey-lighten-2"
        >
            <VContainer>
                <slot />
            </VContainer>
        </VMain>
    </VLayout>
</template>
