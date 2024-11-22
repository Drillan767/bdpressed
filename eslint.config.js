import antfu from '@antfu/eslint-config'

export default antfu({
    stylistic: {
        indent: 4,
        quotes: 'single',
    },
    jsonc: false,
    rules: {
        'vue/valid-v-slot': 'off',
    },
})
