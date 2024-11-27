<script setup lang="ts">
import useUserStore from '@/stores/userStore'
import { AuthError, confirmSignUp } from 'aws-amplify/auth'
import { ref, watch } from 'vue'
import { useDisplay } from 'vuetify'

interface Props {
    email: string
}

const props = defineProps<Props>()

const emit = defineEmits<{
    (e: 'back'): void
    (e: 'success'): void
}>()

const { createUser } = useUserStore()
const { smAndDown } = useDisplay()

const otp = ref('')
const loading = ref(false)
const otpError = ref<string>()

async function confirm() {
    loading.value = true
    try {
        const { isSignUpComplete } = await confirmSignUp({
            username: props.email,
            confirmationCode: otp.value,
        })

        if (isSignUpComplete) {
            await createUser({
                email: props.email,
            })

            emit('success')
        }
    }
    catch (error) {
        if (error instanceof AuthError) {
            if (error.name === 'CodeMismatchException') {
                otpError.value = 'Code de confirmation invalide'
            }
        }
    }
    finally {
        loading.value = false
    }
}

watch(otp, (value) => {
    if (value.length === 6)
        confirm()
})
</script>

<template>
    <VCard
        :width="smAndDown ? '100%' : '560'"
        prepend-icon="mdi-numeric"
        title="Valider l'inscription"
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
    </VCard>
</template>
