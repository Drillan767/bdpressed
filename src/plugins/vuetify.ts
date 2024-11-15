import { createVuetify } from 'vuetify'
import { md3 } from 'vuetify/blueprints'

import 'vuetify/styles'

export default createVuetify({
    blueprint: md3,
    defaults: {
        VTextfield: {
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
    },
})
