<script setup lang="ts">
import { useHead } from '@vueuse/head'
import { signOut } from 'aws-amplify/auth'
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useDisplay } from 'vuetify'

useHead({
    titleTemplate: () => `%s | BédéAdmin ☝️🤓`,
})

const router = useRouter()
const { mobile } = useDisplay()

const openDrawer = ref(true)

async function logout() {
    await signOut()
    router.push('/')
}
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
                            to="/"
                        />
                        <VDivider class="my-2" />
                        <VListItem
                            prepend-icon="mdi-logout"
                            title="Déconnexion"
                            @click="logout"
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
                    to="/administration"
                    prepend-icon="mdi-home"
                    title="Accueil"
                    nav
                    exact
                />
                <VDivider class="my-2" />
                <VListItem
                    to="/administration/articles"
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
                <RouterView />
            </VContainer>
        </VMain>
    </VLayout>
</template>
