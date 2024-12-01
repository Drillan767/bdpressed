<script setup lang="ts">
import type { DataTableHeader } from '@/types'
import type { Schema, SchemaType } from '@root/amplify/data/resource'
import useDayjs from '@/composables/dayjs'
import { useHead } from '@vueuse/head'
import { generateClient } from 'aws-amplify/data'
import { onMounted, ref } from 'vue'

type User = SchemaType<'User'> & { id: string }

useHead({
    title: 'Utilisateurs',
})

const client = generateClient<Schema>()

const { dayjs } = useDayjs()

const users = ref<User[]>([])
const loading = ref(false)
const search = ref<string>()

async function getUsers() {
    const { data: productsData } = await client.models.User.list({
        filter: {
            role: {
                eq: 'user',
            },
        },
    })

    if (productsData) {
        users.value = productsData as User[]
    }
}

const headers: DataTableHeader[] = [
    {
        title: 'Email',
        key: 'email',
        sortable: true,
    },
    {
        title: 'Date d\'inscription',
        key: 'createdAt',
        sortable: true,
    },
    {
        title: 'Actions',
        key: 'actions',
        sortable: false,
        align: 'end',
    },
]

onMounted(getUsers)
</script>

<template>
    <h1 class="mb-2">
        <VIcon icon="mdi-account-group-outline" />
        Utilisateurs
    </h1>
    <VDataTable
        :items="users"
        :headers="headers"
        :search="search"
        :loading="loading ? 'primary' : false"
    >
        <template #top>
            <VContainer>
                <VRow>
                    <VCol
                        cols="12"
                        md="4"
                        class="d-flex justify-end offset-md-8"
                    >
                        <VTextField
                            v-model="search"
                            append-inner-icon="mdi-magnify"
                            label="Rechercher un utilisateur"
                        />
                    </VCol>
                </VRow>
            </VContainer>
        </template>
        <template #item.createdAt="{ item }">
            {{ dayjs(item.createdAt).format('DD/MM/YYYY HH:mm') }}
        </template>
        <template #item.actions="{ item }">
            <div class="d-flex justify-end">
                <RouterLink
                    :to="`/administration/utilisateurs/${item.id}`"
                    class="mx-2"
                >
                    <VBtn
                        variant="text"
                        color="primary"
                        icon="mdi-eye"
                    />
                </RouterLink>
            </div>
        </template>
    </VDataTable>
</template>
