<script setup lang="ts">
import type { DataTableHeader, Money } from '@/types'
import useStatus from '@/Composables/status'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { IllustrationStatus } from '@/types/enums'
import { router } from '@inertiajs/vue3'
import { useHead } from '@vueuse/head'
import { computed, ref } from 'vue'
import { route } from 'ziggy-js'

interface Illustration {
    id: number
    status: IllustrationStatus
    price: Money
    created_at: string
    updated_at: string
    email: string
    order: {
        id: number
        reference: string
    }
}

interface Props {
    illustrations: Illustration[]
}

defineOptions({ layout: AdminLayout })

const props = defineProps<Props>()

const { getIllustrationStatus } = useStatus()

const headers: DataTableHeader[] = [
    {
        title: 'Référence',
        key: 'order.reference',
    },
    {
        title: 'Client',
        key: 'email',
    },
    {
        title: 'Prix',
        key: 'price.formatted',
    },
    {
        title: 'Statut',
        key: 'status',
    },
    {
        title: 'Mise à jour',
        key: 'updated_at',
    },
    {
        title: 'Actions',
        key: 'actions',
        align: 'end',
    },
]

useHead({
    title: 'Illustrations',
})

const filter = ref<'doing' | 'done'>('doing')

const filteredIllustrations = computed(() =>
    props.illustrations.filter(i =>
        filter.value === 'doing'
            ? i.status !== IllustrationStatus.COMPLETED
            : i.status === IllustrationStatus.COMPLETED,
    ),
)
</script>

<template>
    <VRow>
        <VCol
            cols="12"
            md="6"
        >
            <h1 class="mb-4">
                Illustrations
            </h1>
        </VCol>
        <VCol
            class="text-md-end mb-4 mb-md-0"
            cols="12"
            md="6"
        >
            <VBtnToggle v-model="filter">
                <VBtn
                    variant="flat"
                    color="info"
                    class="mr-2"
                    value="doing"
                    rounded="none"
                >
                    En cours
                </VBtn>
                <VBtn
                    variant="flat"
                    color="primary"
                    value="done"
                    rounded="none"
                >
                    Terminées
                </VBtn>
            </VBtnToggle>
        </VCol>
    </VRow>

    <VDataTable
        :items="filteredIllustrations"
        :headers
    >
        <template #item.status="{ item }">
            <VChip
                v-bind="getIllustrationStatus(item.status)"
            />
        </template>
        <template #item.actions="{ item }">
            <VBtn
                v-tooltip="'Voir la commande liée'"
                variant="text"
                color="primary"
                icon="mdi-shopping-search-outline"
                @click="router.visit(route('orders.show', { reference: item.order.reference }))"
            />
            <VBtn
                v-tooltip="'Voir le détail de l\'illustration'"
                variant="text"
                color="blue"
                icon="mdi-eye"
                @click="router.visit(route('admin.illustrations.show', { illustration: item.id }))"
            />
        </template>
    </VDataTable>
</template>
