<script setup lang="ts">
import CartItem from '@/components/shop/CartItem.vue'
import useToast from '@/composables/toast'
import useCartStore from '@/stores/cartStore'
import { useHead } from '@vueuse/head'
import { storeToRefs } from 'pinia'
import { watch } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const { cart } = storeToRefs(useCartStore())
const { showError } = useToast()
const { handleQuantity, removeItem } = useCartStore()

watch(cart, (value) => {
    if (value.length === 0) {
        router.push('/')
            .then(() => showError('Vous devez avoir au moins un article dans votre panier'))
    }
}, { immediate: true })

useHead({
    title: 'Commander',
})
</script>

<template>
    <VContainer>
        <VRow>
            <VCol>
                <VCard class="bede-block">
                    <VCardText>
                        <VContainer>
                            <VRow>
                                <VCol
                                    cols="12"
                                    md="6"
                                >
                                    <h1 class="text-secondary">
                                        Informations de commande
                                    </h1>
                                    <VCard
                                        variant="outlined"
                                        color="secondary"
                                        class="mt-4"
                                    >
                                        <VCardText>
                                            <VIcon icon="mdi-check-circle-outline" />
                                            Le paiement ne se fera que lorsque la commande sera prête à partir ¥
                                        </VCardText>
                                    </VCard>
                                    <!-- <VAlert
                                        class="mt-4"
                                        variant="outlined"
                                        color="secondary"
                                        icon="mdi-information-outline"
                                    >
                                        <span class="my-2">

                                        </span>
                                    </VAlert> -->
                                </VCol>
                                <VCol
                                    cols="12"
                                    md="6"
                                >
                                    <VList>
                                        <CartItem
                                            v-for="(item, i) in cart"
                                            :key="item.id"
                                            :item="item"
                                            @quantity="handleQuantity(i, $event)"
                                            @remove="removeItem(item)"
                                        />
                                    </VList>
                                </VCol>
                            </VRow>
                        </VContainer>
                    </VCardText>
                </VCard>
            </VCol>
        </VRow>
    </VContainer>
</template>

<style scoped lang="scss">

</style>
