<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3'
import { useHead } from '@vueuse/head'
import { provide, ref } from 'vue'
import { useDisplay } from 'vuetify'
import { VListItem } from 'vuetify/components'
import { route } from 'ziggy-js'

useHead({
    titleTemplate: () => `%s | Utilisateur`,
})

const { mobile } = useDisplay()
const page = usePage()

const openDrawer = ref(true)

provide('csrfToken', page.props.csrf_token)
</script>

<template>
    <VLayout>
        <VAppBar
            title="Profil"
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
                    :active="$page.url === '/utilisateur'"
                    prepend-icon="mdi-package-variant"
                    title="Commandes"
                    nav
                    @click="router.visit(route('user.dashboard'))"
                />
                <VListItem
                    prepend-icon="mdi-map-marker-outline"
                    title="Adresses"
                    nav
                    @click="router.visit(route('orders.index'))"
                />
                <VDivider />
                <VListItem
                    prepend-icon="mdi-account-edit"
                    title="Informations personnelles"
                    nav
                    @click="router.visit(route('profile.edit'))"
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
