<script setup lang="ts">
import VisitorsLayout from '@/Layouts/VisitorsLayout.vue'
import useCartStore from '@/Stores/cartStore'
import { useHead } from '@vueuse/head'
import { storeToRefs } from 'pinia'
import { onMounted } from 'vue'

defineOptions({ layout: VisitorsLayout })

useHead({
    title: 'Merci !',
})

const { cart } = storeToRefs(useCartStore())

onMounted(() => {
    cart.value = []
})
</script>

<template>
    <VCard class="bede-block">
        <VCardText class="bede-text">
            <h1 class="text-center mb-4">
                ¥ Merci pour votre achat ! ¥
            </h1>

            <p>
                Comme indiqué précédemment, le paiement ne se fera que lorsque votre commande sera prête à partir !
            </p>

            <p>
                Dans tous les cas, vous avez reçu un email avec le détail de votre commande dedans.
            </p>

            <p>
                À très vite !
            </p>
        </VCardText>
    </VCard>
</template>
