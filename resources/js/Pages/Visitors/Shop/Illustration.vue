<script setup lang="ts">
import VisitorsLayout from '@/Layouts/VisitorsLayout.vue'
import validationConfig from '@/plugins/validationConfig'
import { useHead } from '@vueuse/head'
import { useForm, useIsFormValid } from 'vee-validate'
import { computed, ref, watch } from 'vue'

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
}

interface Form {
    illustrationType: 'bust' | 'full' | 'animal'
    bustDetails?: {
        addedHuman: number
        addedAnimal: number
        pose: 'simple' | 'complex'
        background: 'gradient' | 'simple' | 'complex'
    }
    fullDetails?: {
        addedHuman: number
        addedAnimal: number
        pose: 'simple' | 'complex'
        background: 'gradient' | 'simple' | 'complex'
    }
    animalDetails?: {
        addedAnimal: number
        addedToy: number
        pose: 'simple' | 'complex'
        background: 'gradient' | 'simple' | 'complex'
    }
    options: {
        print: boolean
        addTracking: boolean
        description: string
    }
}

defineOptions({ layout: VisitorsLayout })

const props = defineProps<Props>()

useHead({
    title: 'Commander une illustration',
})

const step = ref(1)
const illustrationType = ref<'bust' | 'full' | 'animal'>('bust')

const { defineField, handleSubmit } = useForm<Form>({
    validationSchema: computed(() => ({
        'illustrationType': 'required',
        'bustDetails.addedHuman': illustrationType.value === 'bust' ? 'required|integer' : '',
        'bustDetails.addedAnimal': illustrationType.value === 'bust' ? 'required|integer' : '',
        'bustDetails.pose': illustrationType.value === 'bust' ? 'required' : '',
        'bustDetails.background': illustrationType.value === 'bust' ? 'required' : '',

        'fullDetails.addedHuman': illustrationType.value === 'full' ? 'required|integer' : '',
        'fullDetails.addedAnimal': illustrationType.value === 'full' ? 'required|integer' : '',
        'fullDetails.pose': illustrationType.value === 'full' ? 'required' : '',
        'fullDetails.background': illustrationType.value === 'full' ? 'required' : '',

        'animalDetails.addedAnimal': illustrationType.value === 'animal' ? 'required|integer' : '',
        'animalDetails.addedToy': illustrationType.value === 'animal' ? 'required|integer' : '',
        'animalDetails.pose': illustrationType.value === 'animal' ? 'required' : '',
        'animalDetails.background': illustrationType.value === 'animal' ? 'required' : '',

        'description': 'required',
    })),
})

const [illustration, illustrationProps] = defineField('illustrationType', validationConfig)
const [bAddedHuman, bAddedHumanProps] = defineField('bustDetails.addedHuman', validationConfig)
const [bAddedAnimal, bAddedAnimalProps] = defineField('bustDetails.addedAnimal', validationConfig)
const [bPose, bPoseProps] = defineField('bustDetails.pose', validationConfig)
const [bBackground, bBackgroundProps] = defineField('bustDetails.background', validationConfig)

const [fAddedHuman, fAddedHumanProps] = defineField('fullDetails.addedHuman', validationConfig)
const [fAddedAnimal, fAddedAnimalProps] = defineField('fullDetails.addedAnimal', validationConfig)
const [fPose, fPoseProps] = defineField('fullDetails.pose', validationConfig)
const [fBackground, fBackgroundProps] = defineField('fullDetails.background', validationConfig)

const [aAddedAnimal, aAddedAnimalProps] = defineField('animalDetails.addedAnimal', validationConfig)
const [aAddedToy, aAddedToyProps] = defineField('animalDetails.addedToy', validationConfig)
const [aPose, aPoseProps] = defineField('animalDetails.pose', validationConfig)
const [aBackground, aBackgroundProps] = defineField('animalDetails.background', validationConfig)

const [print, printProps] = defineField('options.print', validationConfig)
const [addTracking, addTrackingProps] = defineField('options.addTracking', validationConfig)
const [description, descriptionProps] = defineField('options.description', validationConfig)

const formValid = useIsFormValid()

const submit = handleSubmit(async (form) => {
    console.log(form)
})

watch(illustration, (value) => {
    illustrationType.value = value
})
</script>

<template>
    <VCard class="bede-block">
        <VCardText class="bede-text">
            <h1>
                Commander une illustration
            </h1>

            <VContainer class="mt-4">
                <VRow>
                    <VCol
                        cols="12"
                        md="9"
                    >
                        <VStepper
                            v-model="step"
                            :flat="true"
                            class="mt-4"
                            color="primary"
                        >
                            <VStepperHeader>
                                <VStepperItem
                                    :complete="step > 1"
                                    :value="1"
                                    title="Type d'illustration"
                                />
                                <VDivider />
                                <VStepperItem
                                    :complete="step > 2"
                                    :value="2"
                                    title="Détails"
                                />
                                <VDivider />
                                <VStepperItem
                                    :complete="step > 3"
                                    :value="3"
                                    title="Options"
                                />
                                <VDivider />
                                <VStepperItem
                                    :complete="step === 4"
                                    :value="4"
                                    title="Récapitulatif"
                                />
                            </VStepperHeader>
                        </VStepper>
                    </VCol>
                    <VCol
                        cols="12"
                        md="3"
                    >
                        <h2>
                            Total
                        </h2>
                    </VCol>
                </VRow>
            </VContainer>
        </VCardText>
    </VCard>
</template>
