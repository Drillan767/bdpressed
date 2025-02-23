<script setup lang="ts">
import type { IllustrationForm, IllustrationSettings } from '@/types'
import AnimalForm from '@/Components/Shop/illustrations/AnimalForm.vue'
import BustForm from '@/Components/Shop/illustrations/BustForm.vue'
import FullLengthForm from '@/Components/Shop/illustrations/FullLengthForm.vue'
import IllustrationRecap from '@/Components/Shop/illustrations/IllustrationRecap.vue'
import OptionsForm from '@/Components/Shop/illustrations/OptionsForm.vue'
import VisitorsLayout from '@/Layouts/VisitorsLayout.vue'
import validationConfig from '@/plugins/validationConfig'
import useIllustrationStore from '@/Stores/illustrationStore'
import { useHead } from '@vueuse/head'
import { storeToRefs } from 'pinia'
import { useForm, useIsFormValid } from 'vee-validate'
import { computed, onMounted, ref, watch } from 'vue'

interface Props {
    settings: IllustrationSettings
}

type FormDetail = IllustrationForm['bustDetails'] | IllustrationForm['fullDetails'] | IllustrationForm['animalDetails']

defineOptions({ layout: VisitorsLayout })

const props = defineProps<Props>()

useHead({
    title: 'Commander une illustration',
})

const { initSettings } = useIllustrationStore()
const { currentStep, illustrationType } = storeToRefs(useIllustrationStore())

const { defineField, handleSubmit } = useForm<IllustrationForm>({
    validationSchema: {
        illustrationType: 'required',
    },
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
        case 'fl':
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

onMounted(() => {
    initSettings(props.settings)
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
                            v-model="currentStep"
                            :flat="true"
                            class="mt-4"
                            color="primary"
                        >
                            <template #default="{ prev, next }">
                                <VStepperHeader>
                                    <VStepperItem
                                        :complete="currentStep > 1"
                                        :value="1"
                                        title="Type d'illustration"
                                    />
                                    <VDivider />
                                    <VStepperItem
                                        :complete="currentStep > 2"
                                        :value="2"
                                        title="Détails"
                                    />
                                    <VDivider />
                                    <VStepperItem
                                        :complete="currentStep > 3"
                                        :value="3"
                                        title="Options"
                                    />
                                    <VDivider />
                                    <VStepperItem
                                        :complete="currentStep === 4"
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
                                                        value="fl"
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
                                    <VStepperWindowItem :value="2">
                                        <component
                                            :is="detailsFormStep"
                                            v-model:valid="detailsValid"
                                            :settings
                                        />
                                        <!-- <template v-if="illustration === 'bust'">

                                        </template> -->
                                    </VStepperWindowItem>
                                    <VStepperWindowItem :value="3">
                                        <OptionsForm />
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
                        <IllustrationRecap />
                    </VCol>
                </VRow>
            </VContainer>
        </VCardText>
    </VCard>
</template>
