import type { CartIllustration, IllustrationForm, IllustrationSettings } from '@/types'
import { defineStore } from 'pinia'
import { computed, ref } from 'vue'

interface ListItem {
    type?: 'subheader' | 'divider'
    title?: string
    append?: string
}

const translations = {
    step1: {
        bust: 'Buste',
        fl: 'Portrait en pied',
        animal: 'Votre compagnon',
    },
}

const useIllustrationStore = defineStore('illustration', () => {
    const currentStep = ref(1)

    // Step 1
    const illustrationType = ref<'bust' | 'fl' | 'animal'>('bust')

    // Step 2
    const bustForm = ref<Required<IllustrationForm['bustDetails']>>({
        addedHuman: 0,
        addedAnimal: 0,
        pose: 'simple',
        background: 'gradient',
    })
    const flForm = ref<Required<IllustrationForm['fullDetails']>>({
        addedHuman: 0,
        addedAnimal: 0,
        pose: 'simple',
        background: 'gradient',
    })
    const animalForm = ref<Required<IllustrationForm['animalDetails']>>({
        addedHuman: 0,
        addedAnimal: 0,
        addedToy: 0,
        pose: 'simple',
        background: 'gradient',
    })

    const optionsForm = ref<Required<IllustrationForm['options']>>({
        description: '',
        print: false,
        addTracking: false,
    })

    const illustrationSettings = ref<IllustrationSettings>({
        bust_base: 0,
        bust_add_human: 0,
        bust_add_animal: 0,
        fl_base: 0,
        fl_add_human: 0,
        fl_add_animal: 0,
        animal_base: 0,
        animal_add_one: 0,
        animal_toy: 0,
        option_pose_simple: 0,
        option_pose_complex: 0,
        option_bg_gradient: 0,
        option_bg_simple: 0,
        option_bg_complex: 0,
        options_print: 0,
        options_add_tracking: 0,
    })

    const total = computed(() => {
        let total = 0

        total += illustrationSettings.value[`${illustrationType.value}_base`]

        if (illustrationType.value === 'bust' && bustForm.value) {
            total += illustrationSettings.value.bust_add_human * bustForm.value.addedHuman
            total += illustrationSettings.value.bust_add_animal * bustForm.value.addedAnimal
            total += illustrationSettings.value[`option_pose_${bustForm.value.pose}`]
            total += illustrationSettings.value[`option_bg_${bustForm.value.background}`]
        }

        if (illustrationType.value === 'fl' && flForm.value) {
            total += illustrationSettings.value.fl_add_human * flForm.value.addedHuman
            total += illustrationSettings.value.fl_add_animal * flForm.value.addedAnimal
            total += illustrationSettings.value[`option_pose_${flForm.value.pose}`]
            total += illustrationSettings.value[`option_bg_${flForm.value.background}`]
        }

        if (illustrationType.value === 'animal' && animalForm.value) {
            total += illustrationSettings.value.animal_add_one * animalForm.value.addedAnimal
            total += illustrationSettings.value.animal_toy * animalForm.value.addedToy
            total += illustrationSettings.value[`option_pose_${animalForm.value.pose}`]
            total += illustrationSettings.value[`option_bg_${animalForm.value.background}`]
        }

        if (optionsForm.value.addTracking) {
            total += illustrationSettings.value.options_add_tracking
        }

        if (optionsForm.value.print) {
            total += illustrationSettings.value.options_print
        }

        return total
    })

    const recap = computed(() => {
        const list: ListItem[] = [
            {
                type: 'subheader',
                title: 'Type d\'illustration',
            },
            {
                title: `${translations.step1[illustrationType.value]}`,
                append: `${illustrationSettings.value[`${illustrationType.value}_base`]}€`,
            },
        ]

        if (currentStep.value >= 2) {
            list.push(
                {
                    type: 'divider',
                },
                {
                    type: 'subheader',
                    title: 'Détails',
                },
            )

            if (illustrationType.value === 'bust' && bustForm.value) {
                list.push(
                    {
                        title: 'Personnes ajoutées',
                        append: `${illustrationSettings.value.bust_add_human * bustForm.value.addedHuman}€`,
                    },
                    {
                        title: 'Compagnons',
                        append: `${illustrationSettings.value.bust_add_animal * bustForm.value.addedAnimal}€`,
                    },
                    {
                        title: 'Pose',
                        append: `${illustrationSettings.value[`option_pose_${bustForm.value.pose}`]}€`,
                    },
                    {
                        title: 'Fond',
                        append: `${illustrationSettings.value[`option_bg_${bustForm.value.background}`]}€`,
                    },
                )
            }

            if (illustrationType.value === 'fl' && flForm.value) {
                list.push(
                    {
                        title: 'Personnes ajoutées',
                        append: `${illustrationSettings.value.fl_add_human * flForm.value.addedHuman}€`,
                    },
                    {
                        title: 'Compagnons',
                        append: `${illustrationSettings.value.fl_add_animal * flForm.value.addedAnimal}€`,
                    },
                    {
                        title: 'Pose',
                        append: `${illustrationSettings.value[`option_pose_${flForm.value.pose}`]}€`,
                    },
                    {
                        title: 'Fond',
                        append: `${illustrationSettings.value[`option_bg_${flForm.value.background}`]}€`,
                    },
                )
            }

            if (illustrationType.value === 'animal' && animalForm.value) {
                list.push(
                    {
                        title: 'Compagnons ajoutés',
                        append: `${illustrationSettings.value.animal_add_one * animalForm.value.addedAnimal}€`,
                    },
                    {
                        title: 'Jouets ajoutés',
                        append: `${illustrationSettings.value.animal_toy * animalForm.value.addedToy}€`,
                    },
                    {
                        title: 'Pose',
                        append: `${illustrationSettings.value[`option_pose_${animalForm.value.pose}`]}€`,
                    },
                    {
                        title: 'Fond',
                        append: `${illustrationSettings.value[`option_bg_${animalForm.value.background}`]}€`,
                    },
                )
            }
        }

        if (currentStep.value === 3) {
            if (optionsForm.value.addTracking || optionsForm.value.print) {
                list.push(
                    {
                        type: 'divider',
                    },
                    {
                        type: 'subheader',
                        title: 'Options',
                    },
                )
            }

            if (optionsForm.value.addTracking) {
                list.push({
                    title: 'Demander le suivi',
                    append: `${illustrationSettings.value.options_print}€`,
                })
            }

            if (optionsForm.value.print) {
                list.push({
                    title: 'Imprimer l\'illustration',
                    append: `${illustrationSettings.value.options_add_tracking}€`,
                })
            }
        }

        // Total

        list.push(
            {
                type: 'divider',
            },
            {
                title: 'Total',
                append: `${total.value}€`,
            },
        )

        return list
    })

    function initSettings(settings: IllustrationSettings) {
        illustrationSettings.value = settings
    }

    function fillForms(settings: CartIllustration['illustrationSettings']) {
        illustrationType.value = settings.illustrationType

        if (settings.illustrationType === 'bust') {
            bustForm.value = {
                addedHuman: settings.addedHuman,
                addedAnimal: settings.addedAnimal,
                pose: settings.pose,
                background: settings.background,
            }
        }

        else if (settings.illustrationType === 'fl') {
            flForm.value = {
                addedHuman: settings.addedHuman,
                addedAnimal: settings.addedAnimal,
                pose: settings.pose,
                background: settings.background,
            }
        }

        else if (settings.illustrationType === 'animal') {
            animalForm.value = {
                addedHuman: settings.addedHuman,
                addedAnimal: settings.addedAnimal,
                addedToy: settings.addedAnimal,
                pose: settings.pose,
                background: settings.background,
            }
        }

        optionsForm.value = {
            print: settings.print,
            addTracking: settings.addTracking,
            description: settings.description,
        }

        currentStep.value = 3
    }

    return {
        currentStep,
        illustrationType,
        bustForm,
        flForm,
        animalForm,
        optionsForm,
        initSettings,
        fillForms,
        recap,
        total,
    }
}, {
    persist: {
        pick: ['form'],
    },
})

export default useIllustrationStore
