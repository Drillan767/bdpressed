<script setup lang="ts">
import useToast from '@/Composables/toast'
import VisitorsLayout from '@/Layouts/VisitorsLayout.vue'
import { Link, useForm } from '@inertiajs/vue3'
import { useHead } from '@vueuse/head'
import { ref, watch } from 'vue'
import { useDisplay } from 'vuetify'

interface Props {
    status: string | null
    auth: {
        user: any | null
    }
    errors?: Record<string, string>
}

defineOptions({ layout: VisitorsLayout })

const props = defineProps<Props>()

const { smAndDown } = useDisplay()
const { showSuccess } = useToast()

const form = useForm({
    email: '',
    password: '',
    password_confirmation: '',
})

const passwordVisible = ref(false)
const confirmPasswordVisible = ref(false)
const loading = ref(false)
const formValid = ref(true)

function submit() {
    form.post(route('auth.register'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation')
        },
    })
}

useHead({
    title: 'Inscription',
})

watch(() => props.status, (value) => {
    if (value)
        showSuccess(value)
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
                                        v-model="form.email"
                                        :error-messages="errors?.email"
                                        prepend-inner-icon="mdi-at"
                                        label="Email"
                                        type="email"
                                    />
                                </VCol>
                            </VRow>
                            <VRow>
                                <VCol>
                                    <VTextField
                                        v-model="form.password"
                                        :type="passwordVisible ? 'text' : 'password'"
                                        :error-messages="errors?.password"
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
                                        v-model="form.password_confirmation"
                                        :error-messages="errors?.password_confirmation"
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
