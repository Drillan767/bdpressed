<script setup lang="ts">
import type { CartIllustration, CartItem } from '@/types'
import useNumbers from '@/Composables/numbers'
import { router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const { item } = defineProps<{ item: CartItem | CartIllustration }>()
const emit = defineEmits<{
    (e: 'quantity', value: 'decrease' | 'increase'): void
    (e: 'remove'): void
}>()

const { formatPrice } = useNumbers()
</script>

<template>
    <VListItem
        density="comfortable"
    >
        <div class="cart-item">
            <div class="asset">
                <VImg
                    v-if="item.type === 'item'"
                    :src="item.illustration"
                    aspect-ratio="1"
                    width="90px"
                    class="rounded-lg"
                    cover
                />
                <VIcon
                    v-else
                    color="secondary"
                    icon="mdi-palette"
                />
            </div>
            <div class="content">
                <div class="name">
                    {{ item.name }}
                </div>
                <div
                    v-if="item.type === 'item'"
                    class="quantity"
                >
                    <VBtn
                        variant="text"
                        color="secondary"
                        icon="mdi-minus"
                        size="x-small"
                        @click.prevent="emit('quantity', 'decrease')"
                    />
                    {{ item.quantity }}
                    <VBtn
                        :disabled="item.stock === 0"
                        variant="text"
                        color="secondary"
                        icon="mdi-plus"
                        size="x-small"
                        @click.prevent="emit('quantity', 'increase')"
                    />
                </div>
            </div>
            <div class="actions">
                <div>
                    <VBtn
                        v-if="item.type === 'illustration'"
                        variant="text"
                        size="small"
                        icon="mdi-image-edit-outline"
                        @click="router.visit(route('shop.illustration', { illustration: item.id }))"
                    />
                    <VBtn
                        icon="mdi-trash-can-outline"
                        variant="text"
                        size="small"
                        @click.prevent="emit('remove')"
                    />
                </div>

                <span class="price">
                    {{ formatPrice(item.price * item.quantity) }}
                </span>
            </div>
        </div>
    </VListItem>
    <VDivider class="my-4" />
</template>

<style scoped lang="scss">
:deep(.cart-item) {
    display: flex;
    gap: 10px;

    .content {
        .quantity {
            margin-top: 15px;
        }
    }

    .actions {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        align-items: flex-end;
        margin-left: auto;

        .price {
            font-size: 0.625rem;
        }
    }
}
</style>
