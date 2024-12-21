<script setup lang="ts">
import VisitorsLayout from '@/Layouts/VisitorsLayout.vue'
import { useHead } from '@vueuse/head'
import { Link, router  } from '@inertiajs/vue3'
import { useDisplay } from 'vuetify'
import validationConfig from '@/plugins/validationConfig'
import { useForm, useIsFormValid } from 'vee-validate'
import { ref, watch } from 'vue'

interface RegisterForm {
    email: string
    password: string
    password_confirmation: string
}

const { smAndDown } = useDisplay()

const { defineField, handleSubmit, setErrors } = useForm<RegisterForm>({
    validationSchema: {
        email: 'email|required',
        password: {
            required: true,
            regex: /^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_\-+=~`|\\{}[\]:;"'<>,.?/]).{8,}$/,
        },
        password_confirmation: 'required|confirmed:@password',
    },
})

const [email, emailProps] = defineField('email', validationConfig)
const [password, passwordProps] = defineField('password', validationConfig)
const [confirmPassword, confirmPasswordProps] = defineField('password_confirmation', validationConfig)

const formValid = useIsFormValid()

const passwordVisible = ref(false)
const confirmPasswordVisible = ref(false)
const loading = ref(false)

const submit = handleSubmit((form) => {
    router.post('/register', form)
})


/*function submit() {
    form.post(route('register'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation')
        },
    })
}*/

defineOptions({ layout: VisitorsLayout })
useHead({
    title: 'Inscription'
})
</script>

<template>
    <VContainer>
        <VRow>
            <VCol class="d-flex justify-center">
                <VForm>
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
                                        <Link href="/connexion">
                                            Connexion
                                        </Link>
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
                </VForm>
            </VCol>
        </VRow>
    </VContainer>
</template>
