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
    initialValues: {
        illustrationType: 'bust',
        bustDetails: {
            pose: 'simple',
            background: 'gradient',
            addedHuman: 0,
            addedAnimal: 0,
        }
    },
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

const showBAddHumans = ref(false)
const showBAddAnimals = ref(false)
const showFAddHumans = ref(false)
const showFAddAnimals = ref(false)
const showAnimalAddAnimals = ref(false)
const showAnimalAddToys = ref(false)

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
                                        <template v-if="illustration === 'bust'">
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
                                                                    v-bind="bAddedHumanProps"
                                                                    v-model="bAddedHuman"
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
                                                                    v-bind="bAddedHumanProps"
                                                                    v-model="bAddedHuman"
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
                                                                <VListGroup
                                                                    v-bind="bPoseProps"
                                                                    v-model="bPose"
                                                                    mandatory
                                                                >
                                                                    <VRow>
                                                                        <VItem
                                                                            value="simple"
                                                                            v-slot="{ isSelected, toggle }"
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
                                                                                            {{ settings.fl_base }} €
                                                                                        </span>
                                                                                    </template>
                                                                                </VCard>
                                                                            </VCol>
                                                                        </VItem>
                                                                        <VItem
                                                                            value="complex"
                                                                            v-slot="{ isSelected, toggle }"
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
                                                                                            {{ settings.fl_base }} €
                                                                                        </span>
                                                                                    </template>
                                                                                </VCard>
                                                                            </VCol>
                                                                        </VItem>
                                                                    </VRow>
                                                                </VListGroup>
                                                            </template>
                                                        </VCard>
                                                    </VCol>
                                                </VRow>
                                            </VContainer>
                                        </template>
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
