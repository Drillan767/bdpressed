<script setup lang="ts">
import useToast from '@/Composables/toast'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import SettingsLayout from '@/Layouts/SettingsLayout.vue'
import validationConfig from '@/plugins/validationConfig'
import { router } from '@inertiajs/vue3'
import { useHead } from '@vueuse/head'
import { useForm, useIsFormDirty, useIsFormValid } from 'vee-validate'
import { ref, watch } from 'vue'
import { route } from 'ziggy-js'

interface Setting {
    name: string
    category: string
    key: string
    price: string
    stripe_product_id: string | null
    stripe_price_id: string | null
}

interface Props {
    settings: Setting[]
    flash: {
        success: string | null
    }
}

defineOptions({ layout: AdminLayout })

const props = defineProps<Props>()

useHead({
    title: 'Paramètres des illustrations',
})

const { defineField, handleSubmit } = useForm({
    initialValues: Object.fromEntries(
        props.settings.map(setting => [
            setting.key,
            setting.price,
        ]),
    ),
    validationSchema: Object.fromEntries(
        props.settings.map(setting => [
            setting.key,
            'required|numeric',
        ]),
    ),
})

// Create field bindings for each setting
const fields = ref(Object.fromEntries(
    props.settings.map((setting) => {
        const [modelValue, fieldProps] = defineField(setting.key, validationConfig)
        return [setting.key, { modelValue, fieldProps }]
    }),
))

const formDirty = useIsFormDirty()
const formValid = useIsFormValid()

const { showSuccess } = useToast()

const submit = handleSubmit((form) => {
    router.post(route('settings.illustration.update'), form)
})

watch(() => props.flash.success, (value) => {
    if (value)
        showSuccess(value)
}, { immediate: true })
</script>

<template>
    <SettingsLayout>
        <VContainer>
            <VRow>
                <VCol
                    v-for="category in [...new Set(props.settings.map(s => s.category))]"
                    :key="category"
                    cols="12"
                    md="4"
                >
                    <VCard
                        :title="category"
                        class="mt-4"
                        variant="outlined"
                    >
                        <template #text>
                            <VRow>
                                <VCol
                                    v-for="setting in props.settings.filter(s => s.category === category)"
                                    :key="setting.key"
                                    cols="12"
                                >
                                    <VTextField
                                        v-model="fields[setting.key].modelValue"
                                        v-bind="fields[setting.key].fieldProps"
                                        :label="setting.name"
                                        type="number"
                                        suffix="€"
                                    />
                                </VCol>
                            </VRow>
                        </template>
                    </VCard>
                </VCol>
            </VRow>
            <VRow>
                <VCol class="text-end">
                    <VBtn
                        :disabled="!formDirty || !formValid"
                        @click="submit"
                    >
                        Enregistrer
                    </VBtn>
                </VCol>
            </VRow>
        </VContainer>
    </SettingsLayout>
</template>
