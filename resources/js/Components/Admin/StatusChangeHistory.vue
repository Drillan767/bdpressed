<script setup lang="ts">
import type { StatusTriggers, OrderStatus, IllustrationStatus } from '@/types/enums'

interface StatusChangeDisplay {
    id: number
    created_at: string
    reason?: string
    triggered_by: {
        internal: StatusTriggers
        text: string
        color: string
        variant?: 'flat' | 'outlined' | 'tonal'
    }
    from_status?: {
        internal: OrderStatus | IllustrationStatus
        text: string
        color: string
        variant?: 'flat' | 'outlined' | 'tonal'
    }
    to_status: {
        internal: OrderStatus | IllustrationStatus
        text: string
        color: string
        variant?: 'flat' | 'outlined' | 'tonal'
    }
}

defineProps<{
    statusChanges: StatusChangeDisplay[]
}>()
</script>

<template>
    <VCard title="Historique des statuts">
        <template #text>
            <VTimeline
                v-if="statusChanges.length > 0"
                side="end"
                density="compact"
            >
                <VTimelineItem
                    v-for="statusChange in statusChanges"
                    :key="statusChange.id"
                    :dot-color="statusChange.to_status.color"
                    size="small"
                    width="100%"
                >
                    <VCard
                        variant="tonal"
                        color="grey-lighten-4"
                        density="compact"
                    >
                        <template #title>
                            <span class="text-caption text-medium-emphasis">
                                {{ statusChange.created_at }}
                            </span>
                        </template>
                        <template #append>
                            <VChip
                                v-bind="statusChange.triggered_by"
                                size="x-small"
                                variant="outlined"
                            />
                        </template>
                        <template #text>
                            <div>
                                <VChip
                                    v-if="statusChange.from_status"
                                    v-bind="statusChange.from_status"
                                    size="small"
                                />
                                <VIcon
                                    v-if="statusChange.from_status"
                                    icon="mdi-arrow-right"
                                    size="small"
                                    class="text-medium-emphasis"
                                />
                                <VChip
                                    v-bind="statusChange.to_status"
                                    size="small"
                                />
                            </div>
                            <template v-if="statusChange.reason">
                                <VDivider class="my-2" />
                                <span
                                    class="text-caption text-medium-emphasis"
                                >
                                    {{ statusChange.reason }}
                                </span>
                            </template>
                        </template>
                    </VCard>
                </VTimelineItem>
            </VTimeline>
            <div
                v-else
                class="text-center py-8 text-medium-emphasis"
            >
                <VIcon
                    icon="mdi-timeline-clock-outline"
                    size="48"
                    class="mb-2"
                />
                <p>Aucun changement de statut pour le moment</p>
            </div>
        </template>
    </VCard>
</template>
