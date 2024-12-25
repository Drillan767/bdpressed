<script setup lang="ts">
import VisitorsLayout from '@/Layouts/VisitorsLayout.vue'
import { useForm } from '@inertiajs/vue3'
import { useHead } from '@vueuse/head'
import { ref } from 'vue'
import { useDisplay } from 'vuetify'

defineOptions({ layout: VisitorsLayout })

defineProps<{
    auth: {
        user: any | null
    }
    errors?: Record<string, string>
}>()

useHead({
    title: 'Confirmer le mot de passe',
})

const { smAndDown } = useDisplay()

const form = useForm({
    password: '',
})

const passwordVisible = ref(false)

function submit() {
    form.post(route('password.confirm'), {
        onFinish: () => {
            form.reset()
        },
    })
}
</script>

<template>
    <VContainer>
        <VRow>
            <VCol class="d-flex justify-center">
                <VForm>
                    <VCard
                        :width="smAndDown ? '100%' : '560'"
                        prepend-icon="mdi-email-outline"
                        title="Confirmer le mot de passe"
                    >
                        <template #text>
                            <p>
                                Veuillez confirmer votre mot de passe pour continuer.
                            </p>

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
                        </template>
                        <template #actions>
                            <VRow no-gutters>
                                <VCol class="d-flex justify-end">
                                    <VBtn
                                        variant="flat"
                                        @click="submit"
                                    >
                                        Confirmer
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
