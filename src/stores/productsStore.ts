import type { EditProductForm, ProductForm } from '@/types'
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

    async function updateProduct(form: EditProductForm) {
        /*  productsLoading.value = true

        let newPromotedImage = ''
        const newImagesPaths: string[] = []
        const { images, promotedImage, ...fields } = form

        // Load original product's images and promoted image paths.
        const { data: originalProduct } = await client.models.Product.get(
            { id: form.id },
            { selectionSet: ['images', 'promotedImage'] },
        )

        if (!originalProduct) {
            console.error('Impossible de récupérer le produit')
            return
        }

        // Check if the promoted image has changed.
        if (form.promotedImage) {
            const newFileName = form.promotedImage.name
            const oldFilename = originalProduct.promotedImage
                .substring(originalProduct.promotedImage.lastIndexOf('\/') + 1)

            if (newFileName !== oldFilename) {
                // Delete the old file.
                await deleteFiles([`products/${form.id}/${oldFilename}`])

                // Store the new file.
                const promotedImage = await storeSingleFile(form.promotedImage, `products/${form.id}/${newFileName}`)

                if (!promotedImage) {
                    console.error('Impossible de stocker le nouveau promoted image')
                    return
                }

                newPromotedImage = promotedImage
            }
            // filePath.substring(filePath.lastIndexOf('\/')+1);
            // products/252218cb-1bd0-40bd-ac33-4c7202f27f4d/wallpaper_0009.jpg
        }

        // Check if the images have changed.
        if (form.images) {

        } */

        const { data } = await client.models.Product.update({
            id: form.id,
            name: form.name,
            description: form.description,
            price: form.price,
            quickDescription: form.quickDescription,
            updatedAt: new Date().toISOString(),
        })

        if (!data) {
            console.error('Impossible de mettre à jour le produit')
        }
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
        updateProduct,
        deleteProduct,
    }
})

export default useProductsStore
