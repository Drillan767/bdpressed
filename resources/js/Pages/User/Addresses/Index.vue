<script setup lang="ts">
import type { Address } from '@/types'
import CreateAddressDialog from '@/Components/User/Adresses/CreateAddressDialog.vue'
import useToast from '@/Composables/toast'
import UserLayout from '@/Layouts/UserLayout.vue'
import { router } from '@inertiajs/vue3'
import { useHead } from '@vueuse/head'
import { onMounted, ref, watch } from 'vue'
import { VCol } from 'vuetify/components'
import { route } from 'ziggy-js'

defineOptions({ layout: UserLayout })

const props = defineProps<{
    addresses: Address[]
}>()

useHead({
    title: 'Mes adresses',
})

const { showSuccess } = useToast()

const localAddresses = ref<Address[]>([])

const displayAddDialog = ref(false)
const displayEditDialog = ref(false)
const displayDeleteDialog = ref(false)
const shippingLoading = ref(false)
const billingLoading = ref(false)

async function handleDefaultShipping(value: any, addressId: number, type: 'shipping' | 'billing') {
    const newValue = !!value

    if (type === 'shipping') {
        shippingLoading.value = true
    }
    else {
        billingLoading.value = true
    }

    await router.post(route('user.addresses.update-default'), {
        id: addressId,
        value: newValue,
        type,
    })

    shippingLoading.value = false
    billingLoading.value = false

    router.reload()

    showSuccess('Informations enregistrées avec succès')
}

onMounted(() => {
    localAddresses.value = props.addresses
})

watch(() => props.addresses, (addresses) => {
    localAddresses.value = addresses
})
</script>

<template>
    <VContainer>
        <VRow>
            <VCol>
                <h1>
                    <VIcon icon="mdi-map-marker-outline" />
                    Mes adresses
                </h1>
            </VCol>
            <VCol class="text-end">
                <VBtn
                    variant="outlined"
                    prepend-icon="mdi-plus"
                    @click="displayAddDialog = true"
                >
                    Ajouter une adresse
                </VBtn>
            </VCol>
        </VRow>
        <VRow>
            <VCol>
                <VCard>
                    <template #text>
                        <VContainer>
                            <VRow>
                                <VCol v-if="localAddresses.length === 0">
                                    <VEmptyState
                                        icon="mdi-map-marker-remove-outline"
                                        title="Aucune adresse enregistrée"
                                    >
                                        <template #actions>
                                            <VBtn
                                                variant="outlined"
                                                color="primary"
                                                prepend-icon="mdi-plus"
                                                @click="displayAddDialog = true"
                                            >
                                                Ajouter une adresse
                                            </VBtn>
                                        </template>
                                    </VEmptyState>
                                </VCol>
                                <VCol
                                    v-for="(address, i) in localAddresses"
                                    :key="i"
                                    cols="12"
                                    md="3"
                                >
                                    <VCard :title="`Adresse ${i + 1}`">
                                        <template #append>
                                            <VBtn
                                                variant="outlined"
                                                color="error"
                                                icon="mdi-delete"
                                                size="x-small"
                                                @click="displayDeleteDialog = true"
                                            />
                                            <VBtn
                                                variant="outlined"
                                                color="primary"
                                                icon="mdi-pencil"
                                                size="x-small"
                                                class="ml-2"
                                                @click="displayEditDialog = true"
                                            />
                                        </template>
                                        <template #text>
                                            <p class="mb-0">
                                                {{ address.firstName }} {{ address.lastName }}
                                            </p>
                                            <p class="mb-0">
                                                {{ address.street }}
                                            </p>
                                            <p v-if="address.street2">
                                                {{ address.street2 }}
                                            </p>
                                            <p class="mb-0">
                                                {{ address.zipCode }} {{ address.city }} -
                                                {{ address.country }}
                                            </p>
                                        </template>
                                        <template #actions>
                                            <VContainer>
                                                <VDivider class="mb-2" />
                                                <VRow no-gutters>
                                                    <VCol>
                                                        <VSwitch
                                                            v-model="address.default_shipping"
                                                            :loading="shippingLoading"
                                                            :disabled="shippingLoading || address.default_shipping"
                                                            label="Adresse de livraison par défaut"
                                                            density="compact"
                                                            hide-details
                                                            @update:model-value="value => handleDefaultShipping(value, address.id, 'shipping')"
                                                        />
                                                    </VCol>
                                                </VRow>
                                                <VRow no-gutters>
                                                    <VCol>
                                                        <VSwitch
                                                            v-model="address.default_billing"
                                                            :loading="billingLoading"
                                                            :disabled="billingLoading || address.default_billing"
                                                            label="Adresse de facturation par défaut"
                                                            density="compact"
                                                            hide-details
                                                            @update:model-value="value => handleDefaultShipping(value, address.id, 'billing')"
                                                        />
                                                    </VCol>
                                                </VRow>
                                            </VContainer>
                                        </template>
                                    </VCard>
                                </VCol>
                            </VRow>
                        </VContainer>
                    </template>
                </VCard>
            </VCol>
        </VRow>
    </VContainer>
    <CreateAddressDialog
        v-model="displayAddDialog"
        @success="router.reload()"
    />
</template>
