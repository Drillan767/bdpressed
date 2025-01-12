<script setup lang="ts">
import type { AddressFields } from '@/types'
import validationConfig from '@/plugins/validationConfig'
import { useForm, useIsFormValid } from 'vee-validate'
import { watch } from 'vue'

const { title } = defineProps<{ title?: string }>()

const address = defineModel<AddressFields>('address')
const valid = defineModel<boolean>('valid', { required: true })

const { defineField, controlledValues, resetForm } = useForm<AddressFields>({
    validationSchema: {
        firstName: 'required',
        lastName: 'required',
        street: 'required',
        zipCode: 'required',
        country: 'required',
    },
})

const [firstName, firstNameProps] = defineField('firstName', validationConfig)
const [lastName, lastNameProps] = defineField('lastName', validationConfig)
const [addressField, addressProps] = defineField('street', validationConfig)
const [address2Field, address2Props] = defineField('street2', validationConfig)
const [city, cityProps] = defineField('city', validationConfig)
const [zipCode, zipCodeProps] = defineField('zipCode', validationConfig)
const [country, countryProps] = defineField('country', validationConfig)

const formValid = useIsFormValid()

watch(controlledValues, (value) => {
    address.value = value
})

watch(formValid, (value) => {
    valid.value = value
}, { immediate: true })

defineExpose({
    resetForm,
})
</script>

<template>
    <VForm>
        <VContainer>
            <h2
                v-if="title"
                class="text-h5 mb-4"
            >
                {{ title }}
            </h2>
            <VRow no-gutter>
                <VCol
                    cols="12"
                    sm="6"
                >
                    <VTextField
                        v-model="firstName"
                        v-bind="firstNameProps"
                        prepend-inner-icon="mdi-account-outline"
                        label="Prénom *"
                    />
                </VCol>
                <VCol
                    cols="12"
                    md="6"
                >
                    <VTextField
                        v-model="lastName"
                        v-bind="lastNameProps"
                        prepend-inner-icon="mdi-account-outline"
                        label="Nom *"
                    />
                </VCol>
            </VRow>
            <VRow no-gutter>
                <VCol>
                    <VTextField
                        v-model="addressField"
                        v-bind="addressProps"
                        prepend-inner-icon="mdi-map-marker-outline"
                        label="Adresse *"
                    />
                </VCol>
            </VRow>
            <VRow no-gutter>
                <VCol>
                    <VTextField
                        v-model="address2Field"
                        v-bind="address2Props"
                        prepend-inner-icon="mdi-map-marker-outline"
                        label="Adresse complémentaire"
                    />
                </VCol>
            </VRow>
            <VRow no-gutter>
                <VCol
                    cols="12"
                    md="4"
                >
                    <VTextField
                        v-model="zipCode"
                        v-bind="zipCodeProps"
                        prepend-inner-icon="mdi-map-marker-outline"
                        label="Code postal *"
                    />
                </VCol>
                <VCol
                    cols="12"
                    md="4"
                >
                    <VTextField
                        v-model="city"
                        v-bind="cityProps"
                        prepend-inner-icon="mdi-map-marker-outline"
                        label="Ville *"
                    />
                </VCol>
                <VCol
                    cols="12"
                    md="4"
                >
                    <VTextField
                        v-model="country"
                        v-bind="countryProps"
                        prepend-inner-icon="mdi-map-marker-outline"
                        label="Pays *"
                    />
                </VCol>
                <!-- <VCol
                    cols="12"
                    md="4"
                >
                    <VTextField
                        v-model="phoneNumber"
                        v-bind="phoneNumberProps"
                        prepend-inner-icon="mdi-phone-outline"
                        label="Téléphone"
                    />
                </VCol> -->
            </VRow>
        </VContainer>
    </VForm>
</template>
