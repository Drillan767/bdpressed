<script setup lang="ts">
import type { Schema, SchemaType } from '@root/amplify/data/resource'
import { useHead } from '@vueuse/head'
import { generateClient } from 'aws-amplify/data'
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'

type User = SchemaType<'User'> & { id: string }

useHead({
    title: 'Utilisateur',
})

const route = useRoute()
const client = generateClient<Schema>()

const user = ref<User>()

async function getUser() {
    const { data: userData } = await client.models.User.get({
        id: route.params.id.toString(),
    })

    if (userData) {
        user.value = userData as User
    }
}

onMounted(getUser)
</script>

<template>
    <VRow>
        <VCol>
            <h1 class="mb-2">
                <VIcon icon="mdi-account-group-outline" />
                Utilisateurs
            </h1>
        </VCol>
        <VCol class="d-flex justify-end">
            <VBtn
                color="error"
                variant="outlined"
                icon="mdi-delete"
            />
        </VCol>
    </VRow>

    <VCard>
        <template #text>
            <VRow>
                <VCol>
                    <VTextField
                        :model-value="user?.email"
                        label="Email"
                        hide-details
                        disabled
                    />
                </VCol>
            </VRow>
            <VDivider class="my-4" />
            <VRow>
                <VCol cols="12" md="6">
                    <VTextField
                        :model-value="user?.firstName"
                        label="Prénom"
                        hide-details
                        disabled
                    />
                </VCol>

                <VCol cols="12" md="6">
                    <VTextField
                        :model-value="user?.lastName"
                        label="Nom"
                        hide-details
                        disabled
                    />
                </VCol>
            </VRow>
            <VRow>
                <VCol>
                    <VTextField
                        :model-value="user?.address"
                        label="Adresse"
                        hide-details
                        disabled
                    />
                </VCol>
            </VRow>
            <VRow>
                <VCol>
                    <VTextField
                        :model-value="user?.address2"
                        label="Complément d'adresse"
                        hide-details
                        disabled
                    />
                </VCol>
            </VRow>
            <VRow>
                <VCol cols="12" md="6">
                    <VTextField
                        :model-value="user?.city"
                        label="Ville"
                        hide-details
                        disabled
                    />
                </VCol>
                <VCol cols="12" md="6">
                    <VTextField
                        :model-value="user?.zipCode"
                        label="Code postal"
                        hide-details
                        disabled
                    />
                </VCol>
            </VRow>
            <VRow>
                <VCol cols="12" md="6">
                    <VTextField
                        :model-value="user?.country"
                        label="Pays"
                        hide-details
                        disabled
                    />
                </VCol>
                <VCol cols="12" md="6">
                    <VTextField
                        :model-value="user?.phoneNumber"
                        label="Téléphone"
                        hide-details
                        disabled
                    />
                </VCol>
            </VRow>
        </template>
    </VCard>
</template>
