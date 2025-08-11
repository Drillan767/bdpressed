import { localize, setLocale } from '@vee-validate/i18n'
import fr from '@vee-validate/i18n/dist/locale/fr.json'
import { confirmed, email, image, integer, max, min, min_value as minValue, regex, required } from '@vee-validate/rules'
import { configure, defineRule } from 'vee-validate'

setLocale('fr')
localize('fr', fr)

defineRule('confirmed', confirmed)
defineRule('email', email)
defineRule('image', image)
defineRule('max', max)
defineRule('min', min)
defineRule('minValue', minValue)
defineRule('required', required)
defineRule('regex', regex)
defineRule('integer', integer)

// Custom numeric rule that handles both integers and floats
defineRule('numeric', (value: any): boolean | string => {
    if (value === null || value === undefined || value === '') {
        return true
    }

    // Convert to string to handle number inputs
    const stringValue = String(value).trim()

    // Regex for integers and floats (including negative numbers)
    const numericRegex = /^-?(?:\d+(?:\.\d+)?|\.\d+)$/

    if (!numericRegex.test(stringValue)) {
        return 'Ce champ doit être un nombre'
    }

    return true
})

configure({
    generateMessage: localize('fr', {
        messages: {
            ...fr.messages,
            confirmed: 'Les champs ne correspondent pas',
            email: 'Cette adresse email est invalide',
            image: 'Cette image est invalide',
            max: 'Cette valeur est trop grande',
            min: 'Cette valeur est trop petite',
            minValue: 'Cette valeur est trop petite',
            required: 'Ce champ est requis',
            regex: 'Cette valeur est invalide',
            numeric: 'Ce champ doit être un nombre',
        },
    }),
})
