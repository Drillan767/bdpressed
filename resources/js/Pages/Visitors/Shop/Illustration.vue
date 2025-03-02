<script setup lang="ts">
import type { CartIllustration, IllustrationForm, IllustrationSettings } from '@/types'
import AnimalForm from '@/Components/Shop/illustrations/AnimalForm.vue'
import BustForm from '@/Components/Shop/illustrations/BustForm.vue'
import FullLengthForm from '@/Components/Shop/illustrations/FullLengthForm.vue'
import IllustrationRecap from '@/Components/Shop/illustrations/IllustrationRecap.vue'
import OptionsForm from '@/Components/Shop/illustrations/OptionsForm.vue'
import VisitorsLayout from '@/Layouts/VisitorsLayout.vue'
import validationConfig from '@/plugins/validationConfig'
import useCartStore from '@/Stores/cartStore'
import useIllustrationStore from '@/Stores/illustrationStore'
import { router } from '@inertiajs/vue3'
import { useHead } from '@vueuse/head'
import { storeToRefs } from 'pinia'
import { useForm } from 'vee-validate'
import { computed, inject, onMounted, ref, watch } from 'vue'

interface Props {
    settings: IllustrationSettings
}

defineOptions({ layout: VisitorsLayout })

const props = defineProps<Props>()

const openDrawer = inject<() => void>('openDrawer')

useHead({
    title: 'Commander une illustration',
})

const { initSettings, fillForms } = useIllustrationStore()
const {
    currentStep,
    illustrationType,
    total: totalPrice,
    bustForm,
    flForm,
    animalForm,
    optionsForm,
} = storeToRefs(useIllustrationStore())

const { addItem, removeItem } = useCartStore()
const { cart } = storeToRefs(useCartStore())

const optionsValid = ref(false)

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

const submit = handleSubmit(async (form) => {
    const addedHumans = (() => {
        switch (form.illustrationType) {
            case 'bust':
                return bustForm.value.addedHuman
            case 'fl':
                return flForm.value.addedHuman
            case 'animal':
                return animalForm.value.addedHuman
            default:
                return 0
        }
    })()

    const addedAnimals = (() => {
        switch (form.illustrationType) {
            case 'bust':
                return bustForm.value.addedAnimal
            case 'fl':
                return flForm.value.addedAnimal
            case 'animal':
                return animalForm.value.addedToy
            default:
                return 0
        }
    })()

    const pose = (() => {
        switch (form.illustrationType) {
            case 'bust':
                return bustForm.value.pose
            case 'fl':
                return flForm.value.pose
            case 'animal':
                return animalForm.value.pose
            default:
                return 'simple'
        }
    })()

    const background = (() => {
        switch (form.illustrationType) {
            case 'bust':
                return bustForm.value.background
            case 'fl':
                return flForm.value.background
            case 'animal':
                return animalForm.value.background
            default:
                return 'gradient'
        }
    })()

    const illustrationElement: CartIllustration = {
        id: Date.now(),
        name: 'Illustration',
        price: totalPrice.value,
        quantity: 1,
        stock: 0,
        weight: 15,
        illustration: '',
        type: 'illustration',
        illustrationSettings: {
            illustrationType: form.illustrationType,
            print: optionsForm.value.print,
            addTracking: optionsForm.value.addTracking,
            description: optionsForm.value.description,
            addedHuman: addedHumans,
            addedAnimal: addedAnimals,
            pose,
            background,
        },
    }

    openDrawer?.()

    const params = new URLSearchParams(document.location.search)
    const illustration = params.get('illustration')

    if (illustration) {
        const deprecatedIllustration = cart.value.find(item => item.id === Number(illustration))

        if (deprecatedIllustration) {
            removeItem(deprecatedIllustration)
        }
    }

    addItem(illustrationElement)
    router.visit('/boutique')
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

    const params = new URLSearchParams(document.location.search)
    const illustration = params.get('illustration')

    if (!illustration) {
        return
    }

    const retrievedIllustration = cart.value.find(item => item.id === Number(illustration))

    if (!retrievedIllustration || !Object.keys(retrievedIllustration).length || retrievedIllustration.type !== 'illustration') {
        return
    }

    if ('illustrationSettings' in retrievedIllustration) {
        fillForms(retrievedIllustration.illustrationSettings)
    }
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
                                    </VStepperWindowItem>
                                    <VStepperWindowItem :value="3">
                                        <OptionsForm v-model:valid="optionsValid" />
                                    </VStepperWindowItem>
                                </VStepperWindow>
                                <VStepperActions
                                    v-if="currentStep < 3"
                                    @click:next="next"
                                    @click:prev="prev"
                                />
                                <VContainer v-else>
                                    <VRow class="pb-4">
                                        <VCol class="d-flex justify-space-between">
                                            <VBtn
                                                variant="text"
                                                @click="prev"
                                            >
                                                Précédent
                                            </VBtn>
                                            <VBtn
                                                :disabled="!optionsValid"
                                                @click="submit"
                                            >
                                                Ajouter au panier
                                            </VBtn>
                                        </VCol>
                                    </VRow>
                                </VContainer>
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
