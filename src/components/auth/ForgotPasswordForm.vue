<script setup lang="ts">
import validationConfig from '@/plugins/validationConfig'
import { AuthError, resetPassword } from 'aws-amplify/auth'
import { useForm, useIsFormValid } from 'vee-validate'
import { ref } from 'vue'
import { useDisplay } from 'vuetify'

interface ForgotPasswordForm {
    email: string
}

const emit = defineEmits<{
    (e: 'success'): void
}>()

const propsEmail = defineModel<string>({ required: true })

const { defineField, handleSubmit, setErrors } = useForm<ForgotPasswordForm>({
    validationSchema: {
        email: 'email|required',
    },
})

const [email, emailProps] = defineField('email', validationConfig)

const formValid = useIsFormValid()
const { smAndDown } = useDisplay()

const loading = ref(false)

const submit = handleSubmit(async (form) => {
    loading.value = true
    try {
        await resetPassword({
            username: form.email,
        })

        emit('success')
        propsEmail.value = form.email
    }
    catch (error) {
        if (error instanceof AuthError) {
            if (error.name === 'UserNotFoundException') {
                setErrors({
                    email: 'Cet email n\'existe pas',
                })
            }
        }
    }
    finally {
        loading.value = false
    }
})
</script>

<template>
    <VCard
        :width="smAndDown ? '100%' : '560'"
        prepend-icon="mdi-email-outline"
        title="Mot de passe oublié"
    >
        <template #text>
            <VRow>
                <VCol>
                    <VTextField
                        v-bind="emailProps"
                        v-model="email"
                        prepend-inner-icon="mdi-at"
                        label="Email"
                        type="email"
                    />
                </VCol>
            </VRow>
        </template>
        <template #actions>
            <VRow>
                <VCol>
                    <VBtn
                        to="/connexion"
                        variant="text"
                    >
                        Retour
                    </VBtn>
                </VCol>
                <VCol class="d-flex justify-end">
                    <VBtn
                        :disabled="!formValid || loading"
                        :loading
                        variant="flat"
                        @click="submit"
                    >
                        Envoyer
                    </VBtn>
                </VCol>
            </VRow>
        </template>
    </VCard>
</template>
