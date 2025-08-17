<script setup lang="ts">
import type { Money } from '@/types'
import type { IllustrationStatus } from '@/types/enums'
import useStatus from '@/Composables/status'
import useStrings from "@/Composables/strings";
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useHead } from '@vueuse/head'
import { computed, ref } from 'vue'

interface IllustrationDetail {
    name: string
    price: string
}

interface StatusChange {

}

interface Payment {

}

interface Illustration {
    description?: string
    status: IllustrationStatus
    price: Money
    payments: Payment[]
    status_changes: StatusChange[]
    order: {
        reference: string
    }
    created_at: string
    updated_at: string
}

defineOptions({ layout: AdminLayout })

const props = defineProps<{
    illustration: Illustration
    availableStatuses: IllustrationStatus[]
    details: Record<string, IllustrationDetail>
    client: {
        guest: boolean
        email: string
    }
}>()

useHead({
    title: 'Illustration',
})

const { getIllustrationStatus, listIllustrationStatuses } = useStatus()
const { toParagraphs } = useStrings()

const statusList = computed(() => listIllustrationStatuses(props.availableStatuses))

const loading = ref(false)
const status = ref<IllustrationStatus>()

async function updateStatus() {

}
</script>

<template>
    <VContainer>
        <VRow>
            <VCol class="d-flex align-center flex-shrink-1 flex-grow-0">
                <VBtn
                    href="/administration/illustrations"
                    prepend-icon="mdi-arrow-left"
                >
                    Retour
                </VBtn>
            </VCol>
            <VCol>
                <h1>
                    Illustration (#{{ illustration.order.reference }})
                </h1>
                <p>
                    Placée le {{ illustration.created_at }}
                </p>
            </VCol>
        </VRow>
        <VRow>
            <VCol
                cols="12"
                md="8"
            >
                <VRow>
                    <VCol>
                        <VCard
                            :loading="loading"
                            title="Statut de l'illustration"
                        >
                            <template #append>
                                <VChip v-bind="getIllustrationStatus(illustration.status)" />
                            </template>
                            <template #text>
                                <VSelect
                                    v-model="status"
                                    :items="statusList"
                                    item-title="text"
                                    item-value="internal"
                                    variant="outlined"
                                    density="compact"
                                    hide-details
                                    label="Changer le statut"
                                    clearable
                                />
                            </template>
                            <template #actions>
                                <VSpacer />
                                <VBtn
                                    :disabled="!status"
                                    color="primary"
                                    variant="flat"
                                    @click="updateStatus"
                                >
                                    Enregistrer
                                </VBtn>
                            </template>
                        </VCard>
                    </VCol>
                </VRow>
                <VRow>
                    <VCol>
                        <VCard title="Détails de l'illustration">
                            <template #text>
                                <VList>
                                    <VListItem
                                        v-for="(detail, key) in details"
                                        :key="key"
                                        :title="detail.name"
                                    >
                                        <template #append>
                                            {{ detail.price }}
                                        </template>
                                    </VListItem>
                                    <VDivider class="my-4" />
                                    <VListItem
                                        class="font-weight-bold"
                                        title="Prix total"
                                    >
                                        <template #append>
                                            {{ illustration.price.formatted }}
                                        </template>
                                    </VListItem>
                                </VList>
                            </template>
                        </VCard>
                    </VCol>
                </VRow>
                <VRow>
                    <VCol>
                        <VCard
                            title="Description de l'illustration"
                        >
                            <template #text>
                                <div v-html="toParagraphs(illustration.description)" />
                            </template>
                        </VCard>
                    </VCol>
                </VRow>
                <VRow>
                    <VCol>
                        <VCard title="Informations client">
                            <template #text>
                                <VCard variant="tonal">
                                    <template #text>
                                        <p>
                                            <b>Adresse e-mail :</b>
                                            {{ client.email }}
                                        </p>
                                    </template>
                                </VCard>
                            </template>
                        </VCard>
                    </VCol>
                </VRow>
            </VCol>
            <VCol
                cols="12"
                md="4"
            >
                <VRow>
                    <VCol>
                        <VCard title="Historique des statuts">
                            <template #text />
                        </VCard>
                    </VCol>
                </VRow>
                <VRow>
                    <VCol>
                        <VCard title="Historique des paiements">
                            <template #text />
                        </VCard>
                    </VCol>
                </VRow>
            </VCol>
        </VRow>
    </VContainer>
</template>
