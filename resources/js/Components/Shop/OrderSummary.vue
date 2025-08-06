<script setup lang="ts">
import type { Address, OrderStep1Form } from '@/types'
import useStrings from '@/Composables/strings'

interface Props {
    personalInformation: OrderStep1Form
    shippingAddress: Address
    billingAddress: Address
    sameAddress: boolean
}

defineProps<Props>()
const { toParagraphs } = useStrings()
</script>

<template>
    <VRow>
        <VCol>
            <VCard
                title="Informations personnelles"
                variant="tonal"
            >
                <template #text>
                    <p>
                        <b>Email :</b>
                        {{ personalInformation.email }}
                    </p>
                    <p v-if="personalInformation.instagram">
                        <b>Instagram :</b>
                        {{ personalInformation.instagram }}
                    </p>
                    <p v-if="personalInformation.additionalInfos.length > 0">
                        <b>Informations supplémentaires :</b><br>
                        <span v-html="toParagraphs(personalInformation.additionalInfos)" />
                    </p>
                </template>
            </VCard>
        </VCol>
    </VRow>
    <VDivider class="my-4" />
    <VRow>
        <VCol cols="12" md="6">
            <VCard
                title="Adresse de livraison"
                variant="tonal"
            >
                <template #text>
                    <p>
                        {{ shippingAddress.firstName }} {{ shippingAddress.lastName }}
                    </p>
                    <p>
                        {{ shippingAddress.street }}<br>
                        {{ shippingAddress.street2 }}
                    </p>
                    <p>
                        {{ shippingAddress.zipCode }} {{ shippingAddress.city }}
                    </p>
                    <p>
                        {{ shippingAddress.country }}
                    </p>
                </template>
            </VCard>
        </VCol>
        <VCol cols="12" md="6">
            <VCard
                title="Adresse de facturation"
                variant="tonal"
                class="h-100"
            >
                <template #text>
                    <p
                        v-if="sameAddress"
                        class="font-italic"
                    >
                        Identique à l'adresse de livraison
                    </p>
                    <template v-else>
                        <p>
                            {{ billingAddress.firstName }} {{ billingAddress.lastName }}
                        </p>
                        <p>
                            {{ billingAddress.street }}<br>
                            {{ billingAddress.street2 }}
                        </p>
                        <p>
                            {{ billingAddress.zipCode }} {{ billingAddress.city }}
                        </p>
                        <p>
                            {{ billingAddress.country }}
                        </p>
                    </template>
                </template>
            </VCard>
        </VCol>
    </VRow>
</template>
