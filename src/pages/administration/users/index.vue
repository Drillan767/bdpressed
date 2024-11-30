<script setup lang="ts">
import type { DataTableHeader } from '@/types'
import type { Schema, SchemaType } from '@root/amplify/data/resource'
import { useHead } from '@vueuse/head'
import { generateClient } from 'aws-amplify/data'
import { onMounted, ref } from 'vue'

type User = SchemaType<'User'> & { id: string }

useHead({
    title: 'Utilisateurs',
})

const client = generateClient<Schema>()

const users = ref<User[]>([])
const loading = ref(false)

async function getUsers() {
    const { data: productsData } = await client.models.User.list()

    if (productsData) {
        users.value = productsData as User[]
    }
}

const headers: DataTableHeader[] = [
    {
        title: 'Nom',
        key: 'name',
        sortable: true,
    },
    {
        title: 'Prix',
        key: 'price',
        sortable: true,
    },
    {
        title: 'Date d\'inscription',
        key: 'createdAt',
        sortable: true,
    },
    {
        title: '',
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
        :loading="loading ? 'primary' : false"
    />
</template>
