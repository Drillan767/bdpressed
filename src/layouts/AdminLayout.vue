<script setup lang="ts">
import { signOut } from 'aws-amplify/auth'
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useDisplay } from 'vuetify'

const router = useRouter()

const { mobile } = useDisplay()

const openDrawer = ref(true)

async function logout() {
    await signOut()
    router.push('/')
}
</script>

<template>
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
                    <VBtn>
                        <VIcon
                            v-bind="props"
                            color="primary"
                            icon="mdi-account-circle"
                        />
                    </VBtn>
                </template>
                <VList>
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
        </VList>
    </VNavigationDrawer>
    <VMain
        min-height="100vh"
        class="bg-grey-lighten-2 main"
    >
        <VContainer>
            <slot />
        </VContainer>
    </VMain>
</template>
