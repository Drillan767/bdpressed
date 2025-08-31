<script setup lang="ts">
import type { OrderPayment } from '@/types'

defineProps<OrderPayment[]>()
</script>

<template>
    <p>Payment history</p>
</template>
