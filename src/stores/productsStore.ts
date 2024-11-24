import type { ProductForm } from '@/types'
import type { Schema, SchemaType } from '@root/amplify/data/resource'
import useBuckets from '@/composables/buckets'
import { generateClient } from 'aws-amplify/data'
import { defineStore } from 'pinia'
import { ref } from 'vue'

type Product = SchemaType<'Product'>

const useProductsStore = defineStore('products', () => {
    const { storeFiles, storeSingleFile, deleteFiles } = useBuckets()
    const products = ref<Product[]>([])
    const productsLoading = ref(false)
    const client = generateClient<Schema>()

    async function getProducts() {
        productsLoading.value = true
        const { data: productsData } = await client.models.Product.list()

        if (productsData) {
            products.value = productsData
        }

        productsLoading.value = false
    }

    async function storeProducts(form: Required<ProductForm>) {
        productsLoading.value = true

        // Save the product a first time to get the id.
        const { data } = await client.models.Product.create({
            name: form.name,
            description: form.description,
            price: form.price,
            quickDescription: form.quickDescription,
            promotedImage: '',
            images: [],
            createdAt: new Date().toISOString(),
            updatedAt: new Date().toISOString(),
        })

        if (!data) {
            console.error('Impossible de créer le produit')
            return
        }

        // Save the images and the promoted image under the product id directory.
        const promotedImage = await storeSingleFile(form.promotedImage!, `products/${data.id}`)
        const images = await storeFiles(form.images, `products/${data.id}`)

        if (!images || !promotedImage) {
            console.error('Impossible de stocker les images')
            return
        }

        // Update the product with the new images and the promoted image.
        await client.models.Product.update({
            id: data.id,
            promotedImage: promotedImage ?? '',
            images,
        })

        await getProducts()

        productsLoading.value = false
    }

    const getSingleProduct = async (id: string) => {
        productsLoading.value = true
        const { data } = await client.models.Product.get({ id })

        productsLoading.value = false

        return data
    }

    async function deleteProduct(product: Product) {
        productsLoading.value = true

        const images = product.images
            .map(image => `products/${product.id}/${image}`)
            .concat(`products/${product.id}/${product.promotedImage}`)

        await deleteFiles(images)
        await client.models.Product.delete({ id: product.id })
        await getProducts()

        productsLoading.value = false
    }

    return {
        productsLoading,
        products,
        getProducts,
        getSingleProduct,
        storeProducts,
        deleteProduct,
    }
})

export default useProductsStore
