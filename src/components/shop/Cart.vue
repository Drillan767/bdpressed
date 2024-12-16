<script setup lang="ts">
import CartItem from '@/components/shop/CartItem.vue'
import useNumbers from '@/composables/numbers'
import useCartStore from '@/stores/cartStore'
import { storeToRefs } from 'pinia'

const drawer = defineModel<boolean>({ required: true })

const { cart, totalPrice } = storeToRefs(useCartStore())

const { handleQuantity, removeItem } = useCartStore()
const { formatPrice } = useNumbers()
</script>

<template>
    <VNavigationDrawer
        v-model="drawer"
        :temporary="true"
        location="right"
        width="400"
    >
        <VListItem
            title="Panier"
            color="primary"
            class="basket-title bg-primary"
        >
            <template #append>
                <VBtn
                    icon="mdi-close"
                    variant="text"
                    color="white"
                    @click="drawer = false"
                />
            </template>
        </VListItem>

        <div
            v-if="cart.length === 0"
            class="h-75 d-flex align-center justify-center"
        >
            <p class="placeholder text-center">
                Lorsque toudincou,<br> un panier vide. 👁️👄👁️
            </p>
        </div>
        <VList
            v-else
            class="flex-grow-1 flex-shrink-0"
        >
            <CartItem
                v-for="(item, i) in cart"
                :key="item.id"
                :item
                @quantity="handleQuantity(i, $event)"
                @remove="removeItem(item)"
            />
        </VList>
        <div
            v-if="cart.length > 0"
            class="px-4"
        >
            <p class="total">
                Total: {{ formatPrice(totalPrice) }}
            </p>
            <VBtn
                variant="flat"
                color="secondary"
                block
                to="/checkout"
            >
                Commander
            </VBtn>
        </div>
    </VNavigationDrawer>
</template>

<style scoped lang="scss">
:deep(.v-navigation-drawer__content) {
    display: flex;
    flex-direction: column;
    padding-bottom: 15px;
}

.total {
    font-size: 0.825rem;
    text-align: right;
    padding-right: 15px;
}
</style>
