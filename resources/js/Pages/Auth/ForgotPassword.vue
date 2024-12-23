<script setup lang="ts">
import VisitorsLayout from '@/Layouts/VisitorsLayout.vue'
import { useHead } from '@vueuse/head'
import { useForm } from '@inertiajs/vue3'
import useToast from '@/Composables/toast'
import { watch } from 'vue'

const props = defineProps<{
    status?: string
    auth: {
        user: any | null
    }
    errors?: Record<string, string>
}>()

const { showSuccess } = useToast()

const form = useForm({
    email: '',
})

function submit() {
    form.post(route('password.email'))
}

useHead({
    title: 'Mot de passe oublié',
})

defineOptions({ layout: VisitorsLayout })

watch(() => props.status, (value) => {
    if (value) showSuccess(value)
})
</script>

<template>
    <VContainer>
        <VRow>
            <VCol class="d-flex justify-center">
                <VCard
                    :width="smAndDown ? '100%' : '560'"
                    prepend-icon="mdi-email-outline"
                    title="Mot de passe oublié"
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
                                />
                            </VCol>
                        </VRow>
                    </template>
                    <template #actions>
                        <VRow>
                            <VCol>
                                <VBtn
                                    to="/connexion"
                                    variant="text"
                                >
                                    Retour
                                </VBtn>
                            </VCol>
                            <VCol class="d-flex justify-end">
                                <VBtn
                                    :loading
                                    variant="flat"
                                    @click="submit"
                                >
                                    Envoyer
                                </VBtn>
                            </VCol>
                        </VRow>
                    </template>
                </VCard>
            </VCol>
        </VRow>
    </VContainer>
</template>
