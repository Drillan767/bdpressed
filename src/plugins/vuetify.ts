import { createVuetify } from 'vuetify'
import { md3 } from 'vuetify/blueprints'
import { fr } from 'vuetify/locale'

import '@mdi/font/css/materialdesignicons.css'
import 'vuetify/styles/main.css'

export default createVuetify({
    blueprint: md3,
    locale: {
        locale: 'fr',
        fallback: 'fr',
        messages: { fr },
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
        VOtpInput: {
            color: 'primary',
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
})
