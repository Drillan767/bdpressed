<script setup lang="ts">
import type { OrderStep1Form } from '@/types'
import validationConfig from '@/plugins/validationConfig'
import { Link, router } from '@inertiajs/vue3'
import { useForm, useIsFormValid } from 'vee-validate'
import { computed, ref, watch } from 'vue'
import { route } from 'ziggy-js'

interface Props {
    errors?: Record<string, string>
    authenticated: boolean
    showEmailExists: boolean
}

const props = defineProps<Props>()

const valid = defineModel<boolean>('valid', { required: true })
const form = defineModel<OrderStep1Form>('form', { required: true })

const guest = ref(true)
const passwordVisible = ref(false)
const confirmPasswordVisible = ref(false)

const { defineField, controlledValues, setErrors } = useForm<OrderStep1Form>({
    validationSchema: computed(() => ({
        email: 'required|email',
        password: !props.authenticated && !guest.value ? 'required' : '',
        password_confirmation: !props.authenticated && !guest.value ? 'required' : '',
    })),
    initialValues: form.value,
})

const [email, emailProps] = defineField('email', validationConfig)
const [insta, instaProps] = defineField('instagram', validationConfig)
const [password, passwordProps] = defineField('password', validationConfig)
const [passwordConfirmation, passwordConfirmationProps] = defineField('password_confirmation', validationConfig)
const [asGuest, asGuestProps] = defineField('guest', validationConfig)
const [additionalInfos, additionalInfosProps] = defineField('additionalInfos', validationConfig)

const formValid = useIsFormValid()

watch(asGuest, (value) => {
    guest.value = value
})

watch(formValid, (value) => {
    valid.value = value
}, { immediate: true })

watch(controlledValues, (value) => {
    form.value = value
})

watch(() => props.errors, (value) => {
    if (value) {
        if (value['user.email'] && value['user.email'] === 'email exists') {
            setErrors({ email: 'Cette adresse email existe déjà' })
        }
        else {
            setErrors(value)
        }
    }
})
</script>

<template>
    <VContainer>
        <VAlert
            v-if="showEmailExists"
            class="mb-4"
            color="primary"
            variant="tonal"
            icon="mdi-email-alert"
            text="Cette adresse email existe déjà"
        >
            <template #append>
                <VBtn
                    color="primary"
                    variant="flat"
                    @click="router.visit('/connexion')"
                >
                    Connexion
                </VBtn>
            </template>
        </VAlert>
        <VRow no-gutters>
            <VCol>
                <h4>
                    Information de contact
                </h4>
            </VCol>
            <VCol class="text-end">
                <Link
                    v-if="!authenticated"
                    :href="route('login')"
                    class="text-decoration-none"
                >
                    Connexion
                </Link>
            </VCol>
        </VRow>
        <VRow>
            <VCol>
                <VTextField
                    v-bind="emailProps"
                    v-model="email"
                    :disabled="authenticated"
                    label="Adresse e-mail *"
                    type="email"
                />
            </VCol>
        </VRow>
        <VRow>
            <VCol>
                <VTextField
                    v-bind="instaProps"
                    v-model="insta"
                    label="Identifiant instagram"
                    prepend-inner-icon="mdi-at"
                    type="email"
                    hint="exemple: bede.primee"
                />
            </VCol>
        </VRow>
        <VRow
            v-if="!authenticated"
            no-gutters
        >
            <VCol>
                <VSwitch
                    v-bind="asGuestProps"
                    v-model="asGuest"
                    label="Continuer en tant qu'invité ?"
                />
            </VCol>
        </VRow>
        <VRow
            v-if="!authenticated && !asGuest"
            no-gutters
        >
            <VCol
                cols="12"
            >
                <VTextField
                    v-bind="passwordProps"
                    v-model="password"
                    :type="passwordVisible ? 'text' : 'password'"
                    :error-messages="errors?.password"
                    prepend-inner-icon="mdi-lock-outline"
                    hint="8 caractères minimum, 1 majuscule, 1 minuscule, 1 chiffre et 1 caractère spécial"
                    label="Mot de passe *"
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
            <VCol
                cols="12"
            >
                <VTextField
                    v-bind="passwordConfirmationProps"
                    v-model="passwordConfirmation"
                    :error-messages="errors?.password_confirmation"
                    :type="confirmPasswordVisible ? 'text' : 'password'"
                    prepend-inner-icon="mdi-lock-outline"
                    label="Confirmer le mot de passe *"
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

        <VDivider class="mb-4" />

        <VRow>
            <VCol>
                <VTextarea
                    v-bind="additionalInfosProps"
                    v-model="additionalInfos"
                    label="Une demande particulière ?"
                />
            </VCol>
        </VRow>
    </VContainer>
</template>
