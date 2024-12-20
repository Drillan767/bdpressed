import type { EditProductForm, ProductForm, VisitorProduct } from '@/types'
import type { Schema, SchemaType } from '@root/amplify/data/resource'
import useBuckets from '@/composables/buckets'
import useStrings from '@/composables/strings'
import { generateClient } from 'aws-amplify/data'
import { defineStore, storeToRefs } from 'pinia'
import { ref } from 'vue'
import useAuthStore from './authStore'

type Product = SchemaType<'Product'>

const useProductsStore = defineStore('products', () => {
    const { getItems, getSingleItem, storeFiles, storeSingleFile, deleteFiles } = useBuckets()
    const { currentUser } = storeToRefs(useAuthStore())
    const { toSlug } = useStrings()
    const products = ref<Product[]>([])
    const productsLoading = ref(false)
    const client = generateClient<Schema>()

    async function getCatalog() {
        const { data } = await client.models.Product.list({
            authMode: currentUser.value ? 'userPool' : 'identityPool',
            selectionSet: [
                'id',
                'name',
                'slug',
                'price',
                'promotedImage',
                'quickDescription',
            ],
        })

        if (data) {
            const products = data.map(async (product) => {
                const illustration = await getSingleItem(product.promotedImage)
                return { ...product, promotedImage: illustration }
            })

            return Promise.all(products)
        }

        return []
    }

    async function getProducts() {
        productsLoading.value = true
        const { data: productsData } = await client.models.Product.list({
            authMode: 'userPool',
        })

        if (productsData) {
            products.value = productsData as Product[]
        }

        productsLoading.value = false
    }

    async function storeProducts(form: Required<ProductForm>) {
        productsLoading.value = true

        // Save the product a first time to get the id.
        const { data } = await client.models.Product.create({
            name: form.name,
            slug: toSlug(form.name),
            description: form.description,
            price: form.price,
            quickDescription: form.quickDescription,
            promotedImage: '',
            illustrations: [],
            createdAt: new Date().toISOString(),
            updatedAt: new Date().toISOString(),
        }, {
            authMode: 'userPool',
        })

        if (!data) {
            console.error('Impossible de créer le produit')
            return
        }

        // Save the images and the promoted image under the product id directory.
        const promotedImage = await storeSingleFile(form.promotedImage!, `products/${data.id}`)
        const illustrations = await storeFiles(form.illustrations, `products/${data.id}`)

        if (!illustrations || !promotedImage) {
            console.error('Impossible de stocker les images')
            return
        }

        // Update the product with the new images and the promoted image.
        await client.models.Product.update({
            id: data.id,
            promotedImage: promotedImage ?? '',
            illustrations,
        }, {
            authMode: 'userPool',
        })

        await getProducts()

        productsLoading.value = false
    }

    const getSingleProduct = async (id: string) => {
        productsLoading.value = true
        const { data } = await client.models.Product.get(
            { id },
            { authMode: 'userPool' },
        )

        productsLoading.value = false

        return data
    }

    const productBySlug = async (slug: string): Promise<VisitorProduct | null> => {
        productsLoading.value = true

        const { data } = await client.models.Product.listProductBySlug(
            { slug },
            { limit: 1, authMode: currentUser.value ? 'userPool' : 'identityPool' },
        )

        if (!data || data.length === 0) {
            return null
        }

        const product = data[0]

        const promotedImage = await getSingleItem(product.promotedImage)
        const illustrations = await getItems(product.illustrations)

        productsLoading.value = false

        return { ...product, promotedImage, illustrations }
    }

    async function updateProduct(form: EditProductForm) {
        let newPromotedImage: string | undefined

        if (form.promotedImage) {
            newPromotedImage = await storeSingleFile(form.promotedImage, `products/${form.id}/${form.promotedImage.name}`)
        }

        const { data } = await client.models.Product.update({
            id: form.id,
            name: form.name,
            ...form.name && {
                slug: toSlug(form.name),
            },
            description: form.description,
            price: form.price,
            quickDescription: form.quickDescription,
            updatedAt: new Date().toISOString(),
            ...newPromotedImage && {
                promotedImage: newPromotedImage,
            },
        }, {
            authMode: 'userPool',
        })

        if (!data) {
            console.error('Impossible de mettre à jour le produit')
        }
    }

    async function updateProductMedia(files: File[], productId: string) {
        const { data } = await client.models.Product.get(
            { id: productId },
            {
                selectionSet: ['illustrations'],
                authMode: 'userPool',
            },
        )

        if (!data) {
            console.error('Impossible de récupérer le produit')
            return
        }

        const currentImages = data.illustrations

        const newImages = await storeFiles(files, `products/${productId}`)

        if (!newImages) {
            console.error('Impossible de stocker les nouvelles images')
            return
        }

        const newImagesList = currentImages.concat(newImages)

        const { data: updatedProduct } = await client.models.Product.update({
            id: productId,
            illustrations: newImagesList,
        }, {
            authMode: 'userPool',
        })

        if (!updatedProduct) {
            console.error('Impossible de mettre à jour les images du produit')
            return
        }

        const fullPaths = await getItems(newImagesList)

        return fullPaths
    }

    async function removeProductMedia(path: string, productId: string) {
        const { data } = await client.models.Product.get(
            { id: productId },
            {
                selectionSet: ['illustrations'],
                authMode: 'userPool',
            },
        )

        if (!data) {
            console.error('Impossible de récupérer le produit')
            return
        }

        const currentImages = data.illustrations

        const newList = currentImages.filter(illustrations => !path.includes(illustrations))

        await deleteFiles([path])

        const { data: updatedProduct } = await client.models.Product.update({
            id: productId,
            illustrations: newList,
        }, {
            authMode: 'userPool',
        })

        if (!updatedProduct) {
            console.error('Impossible de mettre à jour les images du produit')
            return
        }

        const fullPaths = await getItems(newList)

        return fullPaths
    }

    async function deleteProduct(product: Product) {
        productsLoading.value = true

        const images = product.illustrations
            .concat(product.promotedImage)

        await deleteFiles(images)
        await client.models.Product.delete(
            { id: product.id },
            { authMode: 'userPool' },
        )
        await getProducts()

        productsLoading.value = false
    }

    return {
        productsLoading,
        products,
        getProducts,
        getCatalog,
        getSingleProduct,
        productBySlug,
        storeProducts,
        removeProductMedia,
        updateProductMedia,
        updateProduct,
        deleteProduct,
    }
})

export default useProductsStore
