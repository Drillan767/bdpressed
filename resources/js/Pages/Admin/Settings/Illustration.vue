<script setup lang="ts">
import useToast from '@/Composables/toast'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import SettingsLayout from '@/Layouts/SettingsLayout.vue'
import validationConfig from '@/plugins/validationConfig'
import { router } from '@inertiajs/vue3'
import { useHead } from '@vueuse/head'
import { useForm, useIsFormDirty, useIsFormValid } from 'vee-validate'
import { watch } from 'vue'
import { VTextField } from 'vuetify/components'
import { route } from 'ziggy-js'

interface Props {
    settings: {
        bust_base: number
        bust_add_human: number
        bust_add_animal: number
        fl_base: number
        fl_add_human: number
        fl_add_animal: number
        animal_base: number
        annimal_add_one: number
        animal_toy: number
        option_pose_simple: number
        option_pose_complex: number
        option_bg_gradient: number
        option_bg_simple: number
        option_bg_complex: number
        options_print: number
        options_add_tracking: number

    }
    flash: {
        success: string | null
    }
}

defineOptions({ layout: AdminLayout })

const props = defineProps<Props>()

useHead({
    title: 'Paramètres des illustrations',
})

const { defineField, handleSubmit } = useForm<Props['settings']>({
    validationSchema: {
        bust_base: 'required|integer',
        bust_add_human: 'required|integer',
        bust_add_animal: 'required|integer',
        fl_base: 'required|integer',
        fl_add_human: 'required|integer',
        fl_add_animal: 'required|integer',
        animal_base: 'required|integer',
        annimal_add_one: 'required|integer',
        animal_toy: 'required|integer',
        option_pose_simple: 'required|integer',
        option_pose_complex: 'required|integer',
        option_bg_gradient: 'required|integer',
        option_bg_simple: 'required|integer',
        option_bg_complex: 'required|integer',
        options_print: 'required|integer',
        options_add_tracking: 'required|integer',
    },
    initialValues: props.settings,
})

const [bustBase, bustBaseProps] = defineField('bust_base', validationConfig)
const [bAddHuman, bAddHumanProps] = defineField('bust_add_human', validationConfig)
const [bAddAnimal, bAddAnimalProps] = defineField('bust_add_animal', validationConfig)
const [flBase, flBaseProps] = defineField('fl_base', validationConfig)
const [flAddHuman, flAddHumanProps] = defineField('fl_add_human', validationConfig)
const [flAddAnimal, flAddAnimalProps] = defineField('fl_add_animal', validationConfig)
const [animalBase, animalBaseProps] = defineField('animal_base', validationConfig)
const [annimalAddOne, annimalAddOneProps] = defineField('annimal_add_one', validationConfig)
const [animalToy, animalToyProps] = defineField('animal_toy', validationConfig)
const [poseSimple, poseSimpleProps] = defineField('option_pose_simple', validationConfig)
const [poseComplex, poseComplexProps] = defineField('option_pose_complex', validationConfig)
const [bgGradient, bgGradientProps] = defineField('option_bg_gradient', validationConfig)
const [bgSimple, bgSimpleProps] = defineField('option_bg_simple', validationConfig)
const [bgComplex, bgComplexProps] = defineField('option_bg_complex', validationConfig)
const [optionsPrint, optionsPrintProps] = defineField('options_print', validationConfig)
const [optionsAddTracking, optionsAddTrackingProps] = defineField('options_add_tracking', validationConfig)

const formDirty = useIsFormDirty()
const formValid = useIsFormValid()

const { showSuccess } = useToast()

const submit = handleSubmit(async (form) => {
    router.post(route('settings.illustration.update'), form)
})

watch(() => props.flash.success, (value) => {
    if (value)
        showSuccess(value)
}, { immediate: true })
</script>

<template>
    <SettingsLayout>
        <VContainer>
            <VRow>
                <VCol
                    cols="12"
                    md="4"
                >
                    <VCard
                        class="mt-4"
                        variant="outlined"
                        title="Buste"
                    >
                        <template #text>
                            <VRow>
                                <VCol
                                    cols="12"
                                >
                                    <VTextField
                                        v-bind="bustBaseProps"
                                        v-model="bustBase"
                                        type="number"
                                        suffix="€"
                                        label="Prix de base"
                                    />
                                </VCol>
                                <VCol
                                    cols="12"
                                >
                                    <VTextField
                                        v-bind="bAddHumanProps"
                                        v-model="bAddHuman"
                                        type="number"
                                        suffix="€"
                                        label="Prix par personne ajoutée"
                                    />
                                </VCol>
                                <VCol
                                    cols="12"
                                >
                                    <VTextField
                                        v-bind="bAddAnimalProps"
                                        v-model="bAddAnimal"
                                        type="number"
                                        suffix="€"
                                        label="Prix par animal ajouté"
                                    />
                                </VCol>
                            </VRow>
                        </template>
                    </VCard>
                </VCol>
                <VCol
                    cols="12"
                    md="4"
                >
                    <VCard
                        class="mt-4"
                        variant="outlined"
                        title="Portrait en pied"
                    >
                        <template #text>
                            <VRow>
                                <VCol
                                    cols="12"
                                >
                                    <VTextField
                                        v-bind="flBaseProps"
                                        v-model="flBase"
                                        type="number"
                                        suffix="€"
                                        label="Prix de base"
                                    />
                                </VCol>
                                <VCol
                                    cols="12"
                                >
                                    <VTextField
                                        v-bind="flAddHumanProps"
                                        v-model="flAddHuman"
                                        type="number"
                                        suffix="€"
                                        label="Prix par personne ajoutée"
                                    />
                                </VCol>
                                <VCol
                                    cols="12"
                                >
                                    <VTextField
                                        v-bind="flAddAnimalProps"
                                        v-model="flAddAnimal"
                                        type="number"
                                        suffix="€"
                                        label="Prix par animal ajouté"
                                    />
                                </VCol>
                            </VRow>
                        </template>
                    </VCard>
                </VCol>
                <VCol
                    cols="12"
                    md="4"
                >
                    <VCard
                        class="mt-4"
                        variant="outlined"
                        title="Compagnons"
                    >
                        <template #text>
                            <VRow>
                                <VCol
                                    cols="12"
                                >
                                    <VTextField
                                        v-bind="animalBaseProps"
                                        v-model="animalBase"
                                        type="number"
                                        suffix="€"
                                        label="Prix de base"
                                    />
                                </VCol>
                                <VCol
                                    cols="12"
                                >
                                    <VTextField
                                        v-bind="annimalAddOneProps"
                                        v-model="annimalAddOne"
                                        type="number"
                                        suffix="€"
                                        label="Prix par animal ajoutée"
                                    />
                                </VCol>
                                <VCol
                                    cols="12"
                                >
                                    <VTextField
                                        v-bind="animalToyProps"
                                        v-model="animalToy"
                                        type="number"
                                        suffix="€"
                                        label="Prix d'ajout d'un jouet / doudou"
                                    />
                                </VCol>
                            </VRow>
                        </template>
                    </VCard>
                </VCol>
            </VRow>
            <VRow>
                <VCol
                    cols="12"
                    md="4"
                >
                    <VCard
                        class="mt-4"
                        variant="outlined"
                        title="Pose"
                    >
                        <template #text>
                            <VContainer>
                                <VRow>
                                    <VCol
                                        cols="12"
                                        md="6"
                                    >
                                        <VTextField
                                            v-bind="poseSimpleProps"
                                            v-model="poseSimple"
                                            type="number"
                                            suffix="€"
                                            label="Prix pour pose simple"
                                        />
                                    </vcol>
                                    <VCol
                                        cols="12"
                                        md="6"
                                    >
                                        <VTextField
                                            v-bind="poseComplexProps"
                                            v-model="poseComplex"
                                            type="number"
                                            suffix="€"
                                            label="Prix pour pose complexe"
                                        />
                                    </VCol>
                                </VRow>
                            </VContainer>
                        </template>
                    </VCard>
                </VCol>
                <VCol md="4">
                    <VCard
                        class="mt-4"
                        variant="outlined"
                        title="Fond"
                    >
                        <template #text>
                            <VContainer>
                                <VRow>
                                    <VCol
                                        md="4"
                                    >
                                        <VTextField
                                            v-bind="bgGradientProps"
                                            v-model="bgGradient"
                                            type="number"
                                            suffix="€"
                                            label="Uni / gradient"
                                        />
                                    </VCol>
                                    <VCol
                                        md="4"
                                    >
                                        <VTextField
                                            v-bind="bgSimpleProps"
                                            v-model="bgSimple"
                                            type="number"
                                            suffix="€"
                                            label="Simple"
                                        />
                                    </VCol>
                                    <VCol
                                        cols="12"
                                        md="4"
                                    >
                                        <VTextField
                                            v-bind="bgComplexProps"
                                            v-model="bgComplex"
                                            type="number"
                                            suffix="€"
                                            label="Complexe"
                                        />
                                    </VCol>
                                </VRow>
                            </VContainer>
                        </template>
                    </VCard>
                </VCol>
                <VCol md="4">
                    <VCard
                        class="mt-4"
                        variant="outlined"
                        title="Options"
                    >
                        <template #text>
                            <VContainer>
                                <VRow>
                                    <VCol
                                        cols="12"
                                        md="6"
                                    >
                                        <VTextField
                                            v-bind="optionsPrintProps"
                                            v-model="optionsPrint"
                                            type="number"
                                            suffix="€"
                                            label="Impression de l'illustration"
                                        />
                                    </VCol>
                                    <VCol
                                        cols="12"
                                        md="6"
                                    >
                                        <VTextField
                                            v-bind="optionsAddTrackingProps"
                                            v-model="optionsAddTracking"
                                            type="number"
                                            suffix="€"
                                            label="Demande de suivi"
                                        />
                                    </VCol>
                                </VRow>
                            </VContainer>
                        </template>
                    </VCard>
                </VCol>
            </VRow>
            <VRow>
                <VCol class="text-end">
                    <VBtn
                        :disabled="!formDirty || !formValid"
                        @click="submit"
                    >
                        Enregistrer
                    </VBtn>
                </VCol>
            </VRow>
        </VContainer>
    </SettingsLayout>
</template>
