<script setup lang="ts">
import { AuthError, confirmSignUp } from 'aws-amplify/auth'
import { ref, watch } from 'vue'

interface Props {
    email: string
}

const props = defineProps<Props>()

const emit = defineEmits<{
    (e: 'back'): void
    (e: 'success'): void
}>()

const otp = ref('')
const loading = ref(false)
const otpError = ref<string>()
const displaySuccess = ref(false)

async function confirm() {
    try {
        const { isSignUpComplete } = await confirmSignUp({
            username: props.email,
            confirmationCode: otp.value,
        })

        if (isSignUpComplete)
            emit('success')
    }
    catch (error) {
        if (error instanceof AuthError) {
            if (error.name === 'CodeMismatchException') {
                otpError.value = 'Code de confirmation invalide'
            }
        }
    }
}

watch(otp, (value) => {
    if (value.length === 6)
        confirm()
})
</script>

<template>
    <VCard
        prepend-icon="mdi-numeric"
        title="Valider l'inscription"
        width="560"
    >
        <template #text>
            <VRow>
                <VCol>
                    <VOtpInput
                        v-model="otp"
                        :autofocus="true"
                        prepend-inner-icon="mdi-numeric"
                        label="Code de confirmation"
                        type="number"
                    />
                </VCol>
            </VRow>
        </template>
        <template #actions>
            <VRow>
                <VCol>
                    <VBtn
                        variant="text"
                        @click="emit('back')"
                    >
                        Retour
                    </VBtn>
                </VCol>
                <VCol class="d-flex justify-end">
                    <VBtn
                        :disabled="!otp.length"
                        :loading
                        variant="flat"
                        @click="confirm"
                    >
                        Valider
                    </VBtn>
                </VCol>
            </VRow>
        </template>
        <VSnackbar
            v-model="displaySuccess"
            color="success"
            icon="mdi-check-circle-outline"
            timeout="3000"
            text="Inscription validée, vous pouvez vous connecter"
        />
    </VCard>
</template>
