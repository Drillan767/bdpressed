import { createVuetify } from 'vuetify'
import { md3 } from 'vuetify/blueprints'

import '@mdi/font/css/materialdesignicons.css'
import 'vuetify/styles'

export default createVuetify({
    blueprint: md3,
    defaults: {
        VTextField: {
            color: 'primary',
            variant: 'outlined',
        },
        VTextarea: {
            color: 'primary',
            density: 'comfortable',
        },
        VSwitch: {
            color: 'primary',
            density: 'comfortable',
        },
        VDataTable: {
            mobileBreak: 'sm',
            mobile: null,
        },
        VBtn: {
            color: 'primary',
        },
    },
})
