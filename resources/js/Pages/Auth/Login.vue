<script setup lang="ts">
import VisitorsLayout from '@/Layouts/VisitorsLayout.vue'
import { Link, useForm } from '@inertiajs/vue3'
import { useHead } from '@vueuse/head'
import { useDisplay } from 'vuetify'
import { ref } from 'vue'

interface Props {
    auth: {
        user: any | null
    }
    errors?: Record<string, string>
}

interface LoginForm {
    email: string
    password: string
    remember: boolean
}

const props = defineProps<Props>()

defineOptions({ layout: VisitorsLayout })

useHead({
    title: 'Connexion',
})

const { smAndDown } = useDisplay()

const form = useForm<LoginForm>({
    email: '',
    password: '',
    remember: false,
})

const formValid = ref(true)
const passwordVisible = ref(false)
const loading = ref(false)

async function submit() {
    form.post(route('auth.login'))
}

</script>

<template>
    <VContainer>
        <VRow>
            <VCol class="d-flex justify-center">
                <VForm>
                    <VCard
                        :width="smAndDown ? '100%' : '560'"
                        prepend-icon="mdi-login"
                        title="Connexion"
                    >
                        <template #text>
                            <VRow>
                                <VCol>
                                    <VTextField
                                        v-model="form.email"
                                        prepend-inner-icon="mdi-at"
                                        label="Email"
                                        type="email"
                                        :error-messages="errors.email"
                                    />
                                </VCol>
                            </VRow>
                            <VRow>
                                <VCol>
                                    <VTextField
                                        v-model="form.password"
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
                                        v-model="form.remember"
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
                                        :disabled="!formValid || loading"
                                        :loading="loading"
                                        variant="flat"
                                        @click="submit"
                                    >
                                        Connexion
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
