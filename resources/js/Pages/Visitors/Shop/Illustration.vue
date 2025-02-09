<script setup lang="ts">
import type { IllustrationForm, IllustrationSettings } from '@/types'
import AnimalForm from '@/Components/Shop/illustrations/AnimalForm.vue'
import BustForm from '@/Components/Shop/illustrations/BustForm.vue'
import FullLengthForm from '@/Components/Shop/illustrations/FullLengthForm.vue'
import VisitorsLayout from '@/Layouts/VisitorsLayout.vue'
import validationConfig from '@/plugins/validationConfig'
import { useHead } from '@vueuse/head'
import { useForm, useIsFormValid } from 'vee-validate'
import { computed, ref, watch } from 'vue'

interface Props {
    settings: IllustrationSettings
}

type FormDetail = IllustrationForm['bustDetails'] | IllustrationForm['fullDetails'] | IllustrationForm['animalDetails']

defineOptions({ layout: VisitorsLayout })

defineProps<Props>()

useHead({
    title: 'Commander une illustration',
})

const step = ref(1)
const illustrationType = ref<'bust' | 'full' | 'animal'>('bust')

const { defineField, handleSubmit } = useForm<IllustrationForm>({
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
    initialValues: {
        illustrationType: 'bust',
        bustDetails: {
            addedHuman: 0,
            addedAnimal: 0,
            pose: 'simple',
            background: 'gradient',
        },
    },
})

const [illustration, illustrationProps] = defineField('illustrationType', validationConfig)
/* const [bAddedHuman, bAddedHumanProps] = defineField('bustDetails.addedHuman', validationConfig)
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
const [description, descriptionProps] = defineField('options.description', validationConfig) */

const formValid = useIsFormValid()

const submit = handleSubmit(async (form) => {
    console.log(form)
})

const detailsForm = ref<Required<FormDetail>>({
    addedHuman: 0,
    addedAnimal: 0,
    pose: 'simple',
    background: 'gradient',
})
const detailsValid = ref(false)

const detailsFormStep = computed(() => {
    switch (illustrationType.value) {
        case 'bust':
            return BustForm
        case 'full':
            return FullLengthForm
        case 'animal':
            return AnimalForm
        default:
            return false
    }
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
                            <template #default="{ prev, next }">
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
                                <VStepperWindow>
                                    <VStepperWindowItem
                                        :value="1"
                                    >
                                        <VItemGroup
                                            v-bind="illustrationProps"
                                            v-model="illustration"
                                            mandatory
                                        >
                                            <VContainer>
                                                <VRow>
                                                    <VItem
                                                        v-slot="{ isSelected, toggle }"
                                                        value="bust"
                                                    >
                                                        <VCol
                                                            cols="12"
                                                            md="4"
                                                        >
                                                            <VCard
                                                                :variant="isSelected ? 'tonal' : 'outlined'"
                                                                title="Buste"
                                                                prepend-icon="mdi-account"
                                                                color="primary"
                                                                text="Juste le haut de votre corps !"
                                                                @click="toggle"
                                                            >
                                                                <template #append>
                                                                    <span class="font-weight-bold">
                                                                        {{ settings.bust_base }} €
                                                                    </span>
                                                                </template>
                                                            </VCard>
                                                        </VCol>
                                                    </VItem>
                                                    <VItem
                                                        v-slot="{ isSelected, toggle }"
                                                        value="full"
                                                    >
                                                        <VCol
                                                            cols="12"
                                                            md="4"
                                                        >
                                                            <VCard
                                                                :variant="isSelected ? 'tonal' : 'outlined'"
                                                                color="primary"
                                                                prepend-icon="mdi-human-greeting"
                                                                title="Portrait en pied"
                                                                text="Vous, mais en entier !"
                                                                @click="toggle"
                                                            >
                                                                <template #append>
                                                                    <span class="font-weight-bold">
                                                                        {{ settings.fl_base }} €
                                                                    </span>
                                                                </template>
                                                            </VCard>
                                                        </VCol>
                                                    </VItem>
                                                    <VItem
                                                        v-slot="{ isSelected, toggle }"
                                                        value="animal"
                                                    >
                                                        <VCol
                                                            cols="12"
                                                            md="4"
                                                        >
                                                            <VCard
                                                                :variant="isSelected ? 'tonal' : 'outlined'"
                                                                prepend-icon="mdi-paw"
                                                                color="primary"
                                                                title="Votre compagnon"
                                                                text="Votre compagnon à poil, plume, écailles..."
                                                                @click="toggle"
                                                            >
                                                                <template #append>
                                                                    <span class="font-weight-bold">
                                                                        {{ settings.animal_base }} €
                                                                    </span>
                                                                </template>
                                                            </VCard>
                                                        </VCol>
                                                    </VItem>
                                                </VRow>
                                            </VContainer>
                                        </VItemGroup>
                                    </VStepperWindowItem>
                                    <VStepperWindowItem
                                        :value="2"
                                    >
                                        <component
                                            :is="detailsFormStep"
                                            v-model:form="detailsForm"
                                            v-model:valid="detailsValid"
                                            :settings
                                        />
                                        <!-- <template v-if="illustration === 'bust'">

                                        </template> -->
                                    </VStepperWindowItem>
                                </VStepperWindow>
                                <VStepperActions
                                    @click:next="next"
                                    @click:prev="prev"
                                />
                            </template>
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
