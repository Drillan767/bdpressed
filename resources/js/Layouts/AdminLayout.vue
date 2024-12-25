<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3'
import { useHead } from '@vueuse/head'
import { provide, ref } from 'vue'
import { useDisplay } from 'vuetify'

useHead({
    titleTemplate: () => `%s | BédéAdmin ☝️🤓`,
})

const { mobile } = useDisplay()
const page = usePage()

provide('csrfToken', page.props.csrf_token)

const openDrawer = ref(true)
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
                    prepend-icon="mdi-home"
                    title="Accueil"
                    nav
                    @click="router.visit('/administration')"
                />
                <VDivider class="my-2" />
                <VListItem
                    href="/administration/articles"
                    prepend-icon="mdi-package-variant"
                    title="Articles"
                    nav
                />
                <VListItem
                    to="/administration/utilisateurs"
                    prepend-icon="mdi-account-group-outline"
                    title="Utilisateurs"
                    nav
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
