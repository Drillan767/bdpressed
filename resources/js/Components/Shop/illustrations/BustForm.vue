<script setup lang="ts">
import type { IllustrationDetailed, IllustrationSettings } from '@/types'
import validationConfig from '@/plugins/validationConfig'
import { useForm, useIsFormValid } from 'vee-validate'
import { watch } from 'vue'

defineProps<{ settings: IllustrationSettings }>()

const form = defineModel<IllustrationDetailed>('form', { required: true })
const valid = defineModel<boolean>('valid', { required: true })

const { defineField, controlledValues } = useForm<IllustrationDetailed>({
    validationSchema: {
        addedHUman: 'required|integer',
        addedAnimal: 'required|integer',
        pose: 'required',
        background: 'required',
    },
    initialValues: form.value,
})

const [addedHuman, addedHumanProps] = defineField('addedHuman', validationConfig)
const [addedAnimal, addedAnimalProps] = defineField('addedAnimal', validationConfig)
const [pose, poseProps] = defineField('pose', validationConfig)
const [background, backgroundProps] = defineField('background', validationConfig)

const formValid = useIsFormValid()

watch(controlledValues, (value) => {
    form.value = value
})

watch(formValid, (value) => {
    valid.value = value
})
</script>

<template>
    <VContainer>
        <VRow>
            <VCol>
                <VCard
                    variant="outlined"
                    color="primary"
                    title="Ajouter une personne ?"
                >
                    <template #append>
                        <span class="font-weight-bold text-body-2">
                            {{ settings.bust_add_human }} € / personne
                        </span>
                    </template>
                    <template #text>
                        <VTextField
                            v-bind="addedHumanProps"
                            v-model="addedHuman"
                            :min="0"
                            label="Combien ?"
                            type="number"
                        />
                    </template>
                </VCard>
            </VCol>
            <VCol>
                <VCard
                    variant="outlined"
                    color="primary"
                    title="Ajouter un compagnon ?"
                >
                    <template #append>
                        <span class="font-weight-bold text-body-2">
                            {{ settings.bust_add_animal }} € / compagnon
                        </span>
                    </template>
                    <template #text>
                        <VTextField
                            v-bind="addedAnimalProps"
                            v-model="addedAnimal"
                            :min="0"
                            label="Combien ?"
                            type="number"
                        />
                    </template>
                </VCard>
            </VCol>
        </VRow>
        <VRow>
            <VCol>
                <VCard
                    variant="outlined"
                    color="primary"
                    title="Pose"
                >
                    <template #text>
                        <VItemGroup
                            v-bind="poseProps"
                            v-model="pose"
                            mandatory
                        >
                            <VRow>
                                <VItem
                                    v-slot="{ isSelected, toggle }"
                                    value="simple"
                                >
                                    <VCol
                                        cols="12"
                                        md="4"
                                    >
                                        <VCard
                                            :variant="isSelected ? 'tonal' : 'outlined'"
                                            title="Simple"
                                            prepend-icon="mdi-account"
                                            color="primary"
                                            @click="toggle"
                                        >
                                            <template #append>
                                                <span class="font-weight-bold">
                                                    {{ settings.option_pose_simple }} €
                                                </span>
                                            </template>
                                        </VCard>
                                    </VCol>
                                </VItem>
                                <VItem
                                    v-slot="{ isSelected, toggle }"
                                    value="complex"
                                >
                                    <VCol
                                        cols="12"
                                        md="4"
                                    >
                                        <VCard
                                            :variant="isSelected ? 'tonal' : 'outlined'"
                                            color="primary"
                                            prepend-icon="mdi-human-greeting"
                                            title="Complexe"
                                            @click="toggle"
                                        >
                                            <template #append>
                                                <span class="font-weight-bold">
                                                    {{ settings.option_pose_complex }} €
                                                </span>
                                            </template>
                                        </VCard>
                                    </VCol>
                                </VItem>
                            </VRow>
                        </VItemGroup>
                        <!-- <VListGroup
                            v-bind="poseProps"
                            v-model="pose"
                            mandatory
                        >

                        </VListGroup> -->
                    </template>
                </VCard>
            </VCol>
        </VRow>
    </VContainer>
</template>
