<script setup lang="ts">
import VisitorsLayout from '@/Layouts/VisitorsLayout.vue'
import { useHead } from '@vueuse/head'
import { onMounted} from 'vue'
import { storeToRefs } from 'pinia'
import useCartStore from "@/Stores/cartStore";

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
    <h1>Merci pour votre achat !</h1>
</template>
