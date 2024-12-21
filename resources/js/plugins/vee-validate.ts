import { localize, setLocale } from '@vee-validate/i18n'
import fr from '@vee-validate/i18n/dist/locale/fr.json'
import { confirmed, email, image, integer, max, min, min_value as minValue, numeric, regex, required } from '@vee-validate/rules'
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
defineRule('numeric', numeric)
defineRule('integer', integer)
defineRule('regex', regex)

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
        },
    }),
})
