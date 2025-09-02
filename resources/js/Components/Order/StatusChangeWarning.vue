<script setup lang="ts">
import type { TransitionWarning } from '@/Composables/warnings'
import type { IllustrationStatus, OrderStatus } from '@/types/enums'
import validationConfig from '@/plugins/validationConfig'
import { useForm, useIsFormValid } from 'vee-validate'
import { computed, ref, watch } from 'vue'

interface Props {
    warning?: TransitionWarning<IllustrationStatus | OrderStatus>
}

interface Form {
    payload: string
}

const props = defineProps<Props>()

const emit = defineEmits<{
    (e: 'submit'): void
    (e: 'cancel'): void
}>()

const { defineField, resetForm, controlledValues } = useForm<Form>({
    validationSchema: computed(() => ({
        payload: props.warning?.requiresReason ? 'required|min:10' : '',
    })),
})

const [reason, reasonProp] = defineField('payload', validationConfig)
const formValid = useIsFormValid()

const open = ref(false)

const theme = computed(() => {
    switch (props.warning?.type) {
        case 'info':
            return 'info'
        case 'warning':
            return 'warning'
        case 'destructive':
            return 'error'
        default:
            return 'info'
    }
})

const icon = computed(() => {
    switch (props.warning?.type) {
        case 'info':
            return 'mdi-information'
        case 'warning':
            return 'mdi-alert-outline'
        case 'destructive':
            return 'mdi-alert-circle'
        default:
            return 'mdi-information'
    }
})

function submit() {
    emit('submit')
    open.value = false
    resetForm()
}

function close() {
    emit('cancel')
    open.value = false
    resetForm()
}

watch(() => props.warning, (value) => {
    open.value = !!value
})

defineExpose({
    payload: computed(() => controlledValues.value.payload),
})
</script>

<template>
    <VDialog
        v-model="open"
        persistent
        max-width="600"
    >
        <VCard
            :title="warning?.title"
        >
            <template #prepend>
                <VIcon
                    :color="theme"
                    :icon
                />
            </template>
            <template #text>
                <p>
                    {{ warning?.message }}
                </p>
                <VTextField
                    v-if="warning?.requiresReason"
                    v-bind="reasonProp"
                    v-model="reason"
                    :label="warning.reasonLabel"
                    counter
                />
            </template>
            <template #actions>
                <VSpacer />
                <VBtn
                    variant="text"
                    @click="close"
                >
                    Annuler
                </VBtn>
                <VBtn
                    :color="theme"
                    :disabled="!formValid"
                    variant="flat"
                    @click="submit"
                >
                    Enregistrer
                </VBtn>
            </template>
        </VCard>
    </VDialog>
</template>
