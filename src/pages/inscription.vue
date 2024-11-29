<script setup lang="ts">
import RegisterForm from '@/components/auth/RegisterForm.vue'
import RegisterOTP from '@/components/auth/RegisterOTP.vue'
import useToast from '@/composables/toast'
import { useHead } from '@vueuse/head'
import { ref } from 'vue'
import { useRouter } from 'vue-router'

useHead({
    title: 'Inscription',
})

const router = useRouter()
const { showSuccess } = useToast()

const registerStatus = ref<'form' | 'otp'>('form')
const registerEmail = ref('')

function handleSuccess() {
    router.push('/connexion')
        .then(() => showSuccess('Email validé, vous pouvez vous connecter 🎉'))
}
</script>

<template>
    <VContainer>
        <VRow>
            <VCol class="d-flex justify-center">
                <VTabsWindow v-model="registerStatus">
                    <VTabsWindowItem value="form">
                        <RegisterForm
                            v-model="registerEmail"
                            @success="registerStatus = 'otp'"
                        />
                    </VTabsWindowItem>
                    <VTabsWindowItem value="otp">
                        <RegisterOTP
                            :email="registerEmail"
                            @success="handleSuccess"
                        />
                    </VTabsWindowItem>
                </VTabsWindow>
            </VCol>
        </VRow>
    </VContainer>
</template>
