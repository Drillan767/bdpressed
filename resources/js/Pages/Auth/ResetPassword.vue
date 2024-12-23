<script setup lang="ts">
import VisitorsLayout from '@/Layouts/VisitorsLayout.vue'
import { useForm } from '@inertiajs/vue3'
import { useDisplay } from 'vuetify'
import { ref } from 'vue'

const props = defineProps<{
    email: string
    token: string
    errors?: Record<string, string>
}>()

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
})

const { smAndDown } = useDisplay()

const passwordVisible = ref(false)
const confirmPasswordVisible = ref(false)

function submit() {
    form.post(route('password.store'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation')
        },
    })
}

defineOptions({ layout: VisitorsLayout })
</script>

<template>
    <VContainer>
        <VRow>
            <VCol class="d-flex justify-center">
                <VForm>
                    <VCard
                        :width="smAndDown ? '100%' : '560'"
                        :loading="form.processing"
                        prepend-icon="mdi-login"
                        title="Connexion"
                    >
                        <template #text>
                            <VRow>
                                <VCol>
                                    <VTextField
                                        v-model="form.email"
                                        :error-messages="errors.email"
                                        prepend-inner-icon="mdi-at"
                                        label="Email"
                                        type="email"
                                        disabled
                                    />
                                </VCol>
                            </VRow>
                            <VRow>
                                <VCol>
                                    <VTextField
                                        v-model="form.password"
                                        :error-messages="errors.password"
                                        :type="passwordVisible ? 'text' : 'password'"
                                        prepend-inner-icon="mdi-at"
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
                                        :error-messages="errors.password_confirmation"
                                        :type="confirmPasswordVisible ? 'text' : 'password'"
                                        prepend-inner-icon="mdi-at"
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
                                        :loading="form.processing"
                                        variant="flat"
                                        @click="submit"
                                    >
                                        Réinitialiser le mot de passe
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
