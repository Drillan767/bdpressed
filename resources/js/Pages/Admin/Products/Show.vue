<script setup lang="ts">
import type { AdminProduct } from '@/types'
import DeleteArticleDialog from '@/Components/Admin/Articles/DeleteArticleDialog.vue'
import EditArticleDialog from '@/Components/Admin/Articles/EditArticleDialog.vue'
import EditIllustrationsForm from '@/Components/Admin/Articles/EditIllustrationsForm.vue'
import useStrings from '@/Composables/strings'
import useToast from '@/Composables/toast'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { router } from '@inertiajs/vue3'
import { useHead } from '@vueuse/head'
import { computed, ref } from 'vue'
import { useDisplay } from 'vuetify'

interface Props {
    product: AdminProduct
}

defineOptions({ layout: AdminLayout })
const props = defineProps<Props>()
const { toParagraphs } = useStrings()
const { showSuccess } = useToast()

const promotedImage = ref('')
const displayPreview = ref(false)
const previewUrl = ref('')
const displayEditDialog = ref(false)
const displayDeleteDialog = ref(false)

const { mobile } = useDisplay()

const globalImageHeight = computed(() => mobile.value ? 150 : 300)

function openPreview(url: string) {
    displayPreview.value = true
    previewUrl.value = url
}

function articleDeleted() {
    showSuccess('L\'article a été supprimé')
    router.visit('/administration/articles')
}

useHead({
    title: () => props.product.name,
})
</script>

<template>
    <VContainer>
        <VRow>
            <VCol
                v-if="product"
                cols="12"
                md="8"
            >
                <h1>
                    <VIcon icon="mdi-package-variant" />
                    {{ product.name }} ({{ product.price }} €)
                </h1>
            </VCol>
            <VCol
                cols="12"
                md="4"
                class="text-end"
            >
                <VBtn
                    variant="outlined"
                    icon="mdi-pencil"
                    @click="displayEditDialog = true"
                />
                <VBtn
                    variant="outlined"
                    icon="mdi-delete"
                    class="ml-2"
                    color="error"
                    @click="displayDeleteDialog = true"
                />
            </VCol>
        </VRow>
        <VRow>
            <VCol>
                <VCard v-if="product">
                    <template #text>
                        <VRow>
                            <VCol cols="12" md="6">
                                <p class="text-h6">
                                    Description rapide
                                </p>
                                <p>
                                    {{ product.quickDescription }}
                                </p>
                                <VDivider class="my-4" />
                                <p class="text-h6">
                                    Description
                                </p>
                                <div
                                    class="text-body-2 description"
                                    v-html="toParagraphs(product.description)"
                                />

                                <VDivider class="my-4" />

                                <div>
                                    <p class="text-h6">
                                        Poids
                                    </p>
                                    <p>
                                        {{ product.weight }} grammes
                                    </p>
                                </div>
                            </VCol>
                            <VCol cols="12" md="6">
                                <p class="text-h6 text-center">
                                    Illustration principale
                                </p>
                                <VImg
                                    :src="product.promotedImage"
                                    :height="globalImageHeight"
                                    class="rounded-lg cursor-pointer"
                                    @click="openPreview(promotedImage)"
                                />
                            </vcol>
                        </VRow>
                        <template v-if="product.illustrations.length > 0">
                            <VDivider
                                class="my-4"
                            />

                            <VRow>
                                <VCol cols="12">
                                    <p class="text-h6">
                                        Illustrations
                                    </p>
                                </VCol>
                            </VRow>

                            <EditIllustrationsForm
                                :illustrations="product.illustrations"
                                :product-id="product.id"
                            />
                        </template>
                    </template>
                </VCard>
            </VCol>
        </VRow>
    </VContainer>
    <VDialog
        v-model="displayPreview"
        :width="800"
    >
        <VImg :src="previewUrl" />
    </VDialog>
    <EditArticleDialog
        v-if="product"
        v-model="displayEditDialog"
        :product
        @success="router.visit('/administration/articles')"
    />
    <DeleteArticleDialog
        v-if="product"
        v-model="displayDeleteDialog"
        :product="product"
        @success="articleDeleted"
    />
</template>

<style scoped>
.description :deep(p) {
    margin-bottom: 1rem;
}
</style>
