<script setup lang="ts">
import VisitorsLayout from '@/Layouts/VisitorsLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { useHead } from '@vueuse/head'
import { useForm, useIsFormValid } from 'vee-validate'
import { useDisplay } from 'vuetify'
import { ref, watch } from 'vue'
import validationConfig from '@/plugins/validationConfig'

interface LoginForm {
    email: string
    password: string
    remember: boolean
}

defineProps<{
    canResetPassword?: boolean
    status?: string
}>()

defineOptions({ layout: VisitorsLayout })

useHead({
    title: 'Connexion',
})

const { smAndDown } = useDisplay()


const { defineField, handleSubmit, setErrors } = useForm<LoginForm>({
    validationSchema: {
        email: 'email|required',
        password: 'required',
    },
})

const [email, emailProps] = defineField('email', validationConfig)
const [password, passwordProps] = defineField('password', validationConfig)
const [remember, rememberProps] = defineField('remember')

const formValid = useIsFormValid()

const passwordVisible = ref(false)
const loading = ref(false)

const submit = handleSubmit((form) => {
    router.post('/login', form)
})

</script>

<template>
    <VContainer>
        <VRow>
            <VCol class="d-flex justify-center">
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
                        <VRow>
                            <VCol>
                                <VCheckbox
                                    v-bind="rememberProps"
                                    v-model="remember"
                                    label="Se souvenir de moi"
                                />
                            </VCol>
                        </VRow>
                        <VRow no-gutters>
                            <VCol class="text-right">
                                <Link
                                    href="/oubli-mot-de-passe"
                                    class="text-caption"
                                >
                                    Mot de passe oublié ?
                                </Link>
                            </VCol>
                        </VRow>
                    </template>
                    <template #actions>
                        <VRow no-gutters>
                            <VCol>
                                <p class="text-caption">
                                    Pas encore de compte ?
                                    <Link href="/inscription">
                                        Inscription
                                    </Link>
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
            </VCol>
        </VRow>
    </VContainer>
</template>
