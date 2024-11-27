<script setup lang="ts">
import validationConfig from '@/plugins/validationConfig'
import { AuthError, confirmResetPassword } from 'aws-amplify/auth'
import { useForm, useIsFormValid } from 'vee-validate'
import { ref } from 'vue'
import { useDisplay } from 'vuetify'

interface NewPasswordForm {
    username: string
    confirmationCode: string
    newPassword: string
    confirmPassword: string
}

const emit = defineEmits<{
    (e: 'success'): void
}>()

const propsEmail = defineModel<string>({ required: true })

const { smAndDown } = useDisplay()

const { defineField, handleSubmit, setErrors } = useForm<NewPasswordForm>({
    validationSchema: {
        username: 'email|required',
        confirmationCode: 'required',
        newPassword: {
            required: true,
            regex: /^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_\-+=~`|\\{}[\]:;"'<>,.?/]).{8,}$/,
        },
        confirmPassword: 'required|confirmed:@newPassword',
    },
    initialValues: {
        username: propsEmail.value,
    },
})

const [email, emailProps] = defineField('username', validationConfig)
const [confirmationCode, confirmationCodeProps] = defineField('confirmationCode', validationConfig)
const [password, passwordProps] = defineField('newPassword', validationConfig)
const [confirmPassword, confirmPasswordProps] = defineField('confirmPassword', validationConfig)

const formValid = useIsFormValid()

const passwordVisible = ref(false)
const confirmPasswordVisible = ref(false)
const loading = ref(false)

const submit = handleSubmit(async (form) => {
    loading.value = true
    try {
        const { username, confirmationCode, newPassword } = form
        await confirmResetPassword({
            username,
            confirmationCode,
            newPassword,
        })

        emit('success')
    }
    catch (error) {
        if (error instanceof AuthError) {
            if (error.name === 'UserNotFoundException') {
                setErrors({
                    username: 'Cet email n\'existe pas',
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
        prepend-icon="mdi-account-edit-outline"
        title="Changer le mot de passe"
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
                        disabled
                    />
                </VCol>
            </VRow>
            <VRow>
                <VCol>
                    <p class="text-body-2 font-weight-light">
                        Entrez le code de confirmation reçu par email
                    </p>
                    <VOtpInput
                        v-bind="confirmationCodeProps"
                        v-model="confirmationCode"
                        label="Code de confirmation"
                    />
                </VCol>
            </VRow>
            <VRow>
                <VCol>
                    <VTextField
                        v-bind="passwordProps"
                        v-model="password"
                        :type="passwordVisible ? 'text' : 'password'"
                        prepend-inner-icon="mdi-lock-outline"
                        hint="8 caractères minimum, 1 majuscule, 1 minuscule, 1 chiffre et 1 caractère spécial"
                        label="Mot de passe"
                    >
                        <template #append-inner>
                            <VBtn
                                :icon="passwordVisible ? 'mdi-eye-off-outline' : 'mdi-eye-outline'"
                                variant="text"
                                @click="passwordVisible = !passwordVisible"
                            />
                        </template>
                    </VTextField>
                </VCol>
            </VRow>
            <VRow>
                <VCol>
                    <VTextField
                        v-bind="confirmPasswordProps"
                        v-model="confirmPassword"
                        :type="confirmPasswordVisible ? 'text' : 'password'"
                        prepend-inner-icon="mdi-lock-outline"
                        label="Confirmer le mot de passe"
                    >
                        <template #append-inner>
                            <VBtn
                                :icon="confirmPasswordVisible ? 'mdi-eye-off-outline' : 'mdi-eye-outline'"
                                variant="text"
                                @click="confirmPasswordVisible = !confirmPasswordVisible"
                            />
                        </template>
                    </VTextField>
                </VCol>
            </VRow>
        </template>
        <template #actions>
            <VRow>
                <VCol class="d-flex justify-end">
                    <VBtn
                        :disabled="!formValid || loading"
                        :loading
                        variant="flat"
                        @click="submit"
                    >
                        Changer le mot de passe
                    </VBtn>
                </VCol>
            </VRow>
        </template>
    </VCard>
</template>
