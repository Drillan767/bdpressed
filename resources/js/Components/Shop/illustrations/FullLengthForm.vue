<script setup lang="ts">
import type { IllustrationDetailed, IllustrationSettings } from '@/types'
import validationConfig from '@/plugins/validationConfig'
import useIllustrationStore from '@/Stores/illustrationStore'
import { storeToRefs } from 'pinia'
import { useForm, useIsFormValid } from 'vee-validate'
import { watch } from 'vue'

defineProps<{ settings: IllustrationSettings }>()

const valid = defineModel<boolean>('valid', { required: true })

const { flForm } = storeToRefs(useIllustrationStore())

const { defineField, controlledValues } = useForm<IllustrationDetailed>({
    validationSchema: {
        addedHUman: 'required|integer',
        addedAnimal: 'required|integer',
        pose: 'required',
        background: 'required',
    },
    initialValues: flForm.value,
})

const [addedHuman, addedHumanProps] = defineField('addedHuman', validationConfig)
const [addedAnimal, addedAnimalProps] = defineField('addedAnimal', validationConfig)
const [pose, poseProps] = defineField('pose', validationConfig)
const [background, backgroundProps] = defineField('background', validationConfig)

const formValid = useIsFormValid()

watch(controlledValues, (value) => {
    flForm.value = value
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
                            {{ settings.fl_add_human }} € / personne
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
                            {{ settings.fl_add_animal }} € / compagnon
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
            <VCol
                cols="12"
                md="6"
            >
                <VCard
                    variant="outlined"
                    color="primary"
                    title="Pose"
                    class="fill-height"
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
                                    <VCol cols="12">
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
                                    <VCol cols="12">
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
                    </template>
                </VCard>
            </VCol>
            <VCol
                cols="12"
                md="6"
            >
                <VCard
                    variant="outlined"
                    color="primary"
                    title="Fond"
                >
                    <template #text>
                        <VItemGroup
                            v-bind="backgroundProps"
                            v-model="background"
                            mandatory
                        >
                            <VRow>
                                <VItem
                                    v-slot="{ isSelected, toggle }"
                                    value="gradient"
                                >
                                    <VCol cols="12">
                                        <VCard
                                            :variant="isSelected ? 'tonal' : 'outlined'"
                                            color="primary"
                                            prepend-icon="mdi-gradient-vertical"
                                            title="Dégradé / uni"
                                            @click="toggle"
                                        >
                                            <template #append>
                                                <span class="font-weight-bold">
                                                    {{ settings.option_bg_gradient }} €
                                                </span>
                                            </template>
                                        </VCard>
                                    </VCol>
                                </VItem>
                                <VItem
                                    v-slot="{ isSelected, toggle }"
                                    value="simple"
                                >
                                    <VCol cols="12">
                                        <VCard
                                            :variant="isSelected ? 'tonal' : 'outlined'"
                                            color="primary"
                                            prepend-icon="mdi-square-outline"
                                            title="Simple"
                                            @click="toggle"
                                        >
                                            <template #append>
                                                <span class="font-weight-bold">
                                                    {{ settings.option_bg_simple }} €
                                                </span>
                                            </template>
                                        </VCard>
                                    </VCol>
                                </VItem>
                                <VItem
                                    v-slot="{ isSelected, toggle }"
                                    value="complex"
                                >
                                    <VCol cols="12">
                                        <VCard
                                            :variant="isSelected ? 'tonal' : 'outlined'"
                                            color="primary"
                                            prepend-icon="mdi-image-area"
                                            title="Complexe"
                                            @click="toggle"
                                        >
                                            <template #append>
                                                <span class="font-weight-bold">
                                                    {{ settings.option_bg_complex }} €
                                                </span>
                                            </template>
                                        </VCard>
                                    </VCol>
                                </VItem>
                            </VRow>
                        </VItemGroup>
                    </template>
                </VCard>
            </VCol>
        </VRow>
    </VContainer>
</template>
