<script setup lang="ts">
import type { IllustrationForm } from '@/types'
import validationConfig from '@/Plugins/validationConfig'
import useIllustrationStore from '@/Stores/illustrationStore'
import { storeToRefs } from 'pinia'
import { useForm, useIsFormValid } from 'vee-validate'
import { watch } from 'vue'

const valid = defineModel<boolean>('valid', { required: true })

const { optionsForm } = storeToRefs(useIllustrationStore())

const { defineField, controlledValues } = useForm<IllustrationForm['options']>({
    validationSchema: {
        description: 'required|min:10',
    },
    initialValues: optionsForm.value,
})

const [description, descriptionProps] = defineField('description', validationConfig)
const [print, printProps] = defineField('print', validationConfig)
const [addTracking, addTrackingProps] = defineField('addTracking', validationConfig)

const formValid = useIsFormValid()

watch(controlledValues, (value) => {
    optionsForm.value = value
})

watch(formValid, (value) => {
    valid.value = value
}, { immediate: true })
</script>

<template>
    <VContainer>
        <VRow>
            <VCol
                cols="12"
                md="6"
            >
                <VItemGroup
                    v-bind="printProps"
                    v-model="print"
                >
                    <VItem
                        v-slot="{ isSelected, toggle }"
                        :value="true"
                    >
                        <VCard
                            title="Imprimer l'illustration"
                            :prepend-icon="isSelected ? 'mdi-checkbox-marked-outline' : 'mdi-checkbox-blank-outline'"
                            :variant="isSelected ? 'tonal' : 'outlined'"
                            color="primary"
                            @click="toggle"
                        />
                    </VItem>
                </VItemGroup>
            </VCol>
            <VCol
                cols="12"
                md="6"
            >
                <VItemGroup
                    v-bind="addTrackingProps"
                    v-model="addTracking"
                >
                    <VItem
                        v-slot="{ isSelected, toggle }"
                        :value="true"
                    >
                        <VCard
                            title="Demander le suivi"
                            :prepend-icon="isSelected ? 'mdi-checkbox-marked-outline' : 'mdi-checkbox-blank-outline'"
                            :variant="isSelected ? 'tonal' : 'outlined'"
                            color="primary"
                            @click="toggle"
                        />
                    </VItem>
                </VItemGroup>
            </VCol>
        </VRow>
        <VRow>
            <VCol>
                <VTextarea
                    v-bind="descriptionProps"
                    v-model="description"
                    label="Description"
                    placeholder="Décrivez votre super projet"
                    rows="3"
                    variant="outlined"
                    color="primary"
                />
            </VCol>
        </VRow>
    </VContainer>
</template>
