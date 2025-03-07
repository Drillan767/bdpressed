import { createVuetify } from 'vuetify'
import { md3 } from 'vuetify/blueprints'
import { VNumberInput } from 'vuetify/labs/VNumberInput'
import { VStepperVertical } from 'vuetify/labs/VStepperVertical'
import { fr } from 'vuetify/locale'

import '@mdi/font/css/materialdesignicons.css'
import 'vuetify/styles'

export default createVuetify({
    blueprint: md3,

    locale: {
        locale: 'fr',
        fallback: 'fr',
        messages: { fr },
    },

    components: {
        VStepperVertical,
        VNumberInput,
    },

    defaults: {
        VTextField: {
            color: 'primary',
            variant: 'outlined',
        },
        VTextarea: {
            color: 'primary',
            variant: 'outlined',
        },
        VFileInput: {
            color: 'primary',
            variant: 'outlined',
        },
        VSwitch: {
            color: 'primary',
        },
        VDataTable: {
            mobileBreak: 'sm',
            mobile: null,
            hover: true,
        },
        VCardTitle: {
            class: 'text-h5 font-weight-bold',
        },
        VBtn: {
            color: 'primary',
        },
    },
    theme: {
        themes: {
            light: {
                dark: false,
                colors: {
                    primary: '#ff802b',
                    secondary: '#f900a1',
                    accent: '#0f74ff',
                },
            },
        },
    },
})
