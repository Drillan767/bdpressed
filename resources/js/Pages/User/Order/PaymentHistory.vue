<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { route } from 'ziggy-js'
import { PaymentStatus } from '@/types/enums'

interface History {

}

const loading = ref(false)

const test = ref([])

async function fetchHistory() {
    loading.value = true

    test.value = await fetch(route('user.payment-history'))
        .then(response => response.json())
        .then(data => data)

    loading.value = false
}

onMounted(fetchHistory);
</script>

<template>
    <VCard
        v-if="test.length > 0"
        :loading
        title="Historique des paiements"
    >
        <template #text>
            {{ test }}
        </template>
    </VCard>
</template>
