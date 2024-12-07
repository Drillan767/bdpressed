<script setup lang="ts">
import validationConfig from '@/plugins/validationConfig'
import useAuthStore from '@/stores/authStore'
import { onKeyStroke } from '@vueuse/core'
import { AuthError, resendSignUpCode, signIn } from 'aws-amplify/auth'
import { storeToRefs } from 'pinia'
import { useForm, useIsFormValid } from 'vee-validate'
import { ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useDisplay } from 'vuetify'

interface LoginForm {
    email: string
    password: string
}

const emit = defineEmits<{
    (e: 'changeStep', email: string): void
}>()

const router = useRouter()
const { currentUser, loadingUser } = storeToRefs(useAuthStore())
const { smAndDown } = useDisplay()

const { defineField, handleSubmit, setErrors } = useForm<LoginForm>({
    validationSchema: {
        email: 'email|required',
        password: 'required',
    },
})

const [email, emailProps] = defineField('email', validationConfig)
const [password, passwordProps] = defineField('password', validationConfig)

const formValid = useIsFormValid()

const passwordVisible = ref(false)
const loading = ref(false)
const loginSuccess = ref(false)

const submit = handleSubmit(async (form) => {
    loading.value = true
    try {
        const result = await signIn({
            username: form.email,
            password: form.password,
        })

        if (result.nextStep.signInStep === 'CONFIRM_SIGN_UP') {
            await resendSignUpCode({
                username: form.email,
            })
            emit('changeStep', form.email)
        }

        if (result.isSignedIn) {
            loginSuccess.value = true
        }
    }
    catch (error) {
        if (error instanceof AuthError) {
            if (error.name === 'NotAuthorizedException') {
                setErrors({
                    email: 'Identifiant ou mot de passe incorrect',
                    password: 'Identifiant ou mot de passe incorrect',
                })
            }
        }
    }
    finally {
        loading.value = false
    }

    loading.value = false
})

onKeyStroke('Enter', submit)

watch([loginSuccess, currentUser], ([success, user]) => {
    if (success && user) {
        router.push(user.role === 'admin' ? '/administration' : '/utilisateur')
    }
})

defineExpose({ submit })
</script>

<template>
    <VCard
        :width="smAndDown ? '100%' : '560'"
        prepend-icon="mdi-login"
        title="Connexion"
    >
        <template #text>
            <VRow>
                <VCol>
                    <VTextField
                        v-model="email"
                        v-bind="emailProps"
                        prepend-inner-icon="mdi-at"
                        label="Email"
                        type="email"
                    />
                </VCol>
            </VRow>
            <VRow>
                <VCol>
                    <VTextField
                        v-model="password"
                        v-bind="passwordProps"
                        :type="passwordVisible ? 'text' : 'password'"
                        prepend-inner-icon="mdi-lock-outline"
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
            <VRow no-gutters>
                <VCol class="text-right">
                    <RouterLink
                        to="/oubli-mot-de-passe"
                        class="text-caption"
                    >
                        Mot de passe oublié ?
                    </RouterLink>
                </VCol>
            </VRow>
        </template>
        <template #actions>
            <VRow no-gutters>
                <VCol>
                    <p class="text-caption">
                        Pas encore de compte ?
                        <RouterLink to="/inscription">
                            Inscription
                        </RouterLink>
                    </p>
                </VCol>
            </VRow>
            <VRow no-gutters>
                <VCol class="d-flex justify-end">
                    <VBtn
                        :disabled="!formValid || loading || loadingUser"
                        :loading="loading || loadingUser"
                        variant="flat"
                        @click="submit"
                    >
                        Connexion
                    </VBtn>
                </VCol>
            </VRow>
        </template>
    </VCard>
</template>
