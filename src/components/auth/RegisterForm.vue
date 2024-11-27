<script setup lang="ts">
import useToast from '@/composables/toast'
import validationConfig from '@/plugins/validationConfig'
import { AuthError, signUp } from 'aws-amplify/auth'
import { useForm, useIsFormValid } from 'vee-validate'
import { ref, watch } from 'vue'
import { useDisplay } from 'vuetify'

interface RegisterForm {
    email: string
    password: string
    confirmPassword: string
}

const emit = defineEmits<{
    (e: 'success'): void
}>()

const propsEmail = defineModel<string>({ required: true })

const { showSuccess } = useToast()
const { smAndDown } = useDisplay()

const { defineField, handleSubmit, setErrors } = useForm<RegisterForm>({
    validationSchema: {
        email: 'email|required',
        password: {
            required: true,
            regex: /^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_\-+=~`|\\{}[\]:;"'<>,.?/]).{8,}$/,
        },
        confirmPassword: 'required|confirmed:@password',
    },
})

const [email, emailProps] = defineField('email', validationConfig)
const [password, passwordProps] = defineField('password', validationConfig)
const [confirmPassword, confirmPasswordProps] = defineField('confirmPassword', validationConfig)

const formValid = useIsFormValid()

const passwordVisible = ref(false)
const confirmPasswordVisible = ref(false)
const loading = ref(false)

const submit = handleSubmit(async (form) => {
    loading.value = true
    try {
        await signUp({
            username: form.email,
            password: form.password,
            options: {
                userAttributes: {
                    'custom:role': 'admin',
                },
            },
        })

        showSuccess('Bienvenue 🎉 Vous pouvez maintenant valider votre inscription.')

        emit('success')
    }
    catch (error) {
        console.error(error)
        if (error instanceof AuthError) {
            if (error.name === 'UsernameExistsException') {
                setErrors({
                    email: 'Cet email est déjà utilisé',
                })
            }
        }
    }
    finally {
        loading.value = false
    }
})

watch([email, formValid], ([email, valid]) => {
    if (valid && email.length > 0) {
        propsEmail.value = email
    }
})
</script>

<template>
    <VCard
        :width="smAndDown ? '100%' : '560'"
        prepend-icon="mdi-account-plus"
        title="Inscription"
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
                <VCol>
                    <p class="text-caption">
                        Déjà inscrit ?
                        <RouterLink to="/connexion">
                            Connexion
                        </RouterLink>
                    </p>
                </VCol>
            </VRow>
            <VRow>
                <VCol class="d-flex justify-end">
                    <VBtn
                        :disabled="!formValid || loading"
                        :loading
                        variant="flat"
                        @click="submit"
                    >
                        Inscription
                    </VBtn>
                </VCol>
            </VRow>
        </template>
    </VCard>
</template>
