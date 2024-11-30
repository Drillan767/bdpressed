import { defineStore } from 'pinia'
import { ref } from 'vue'

interface CartItem {
    id: string
    name: string
    price: number
    quantity: number
    illustration: string
}

const useCartStore = defineStore('cart', () => {
    const cart = ref<CartItem[]>([])

    function addItem(item: CartItem) {
        cart.value.push(item)
    }

    function removeItem(item: CartItem) {
        cart.value.splice(cart.value.indexOf(item), 1)
    }

    return {
        cart,
        addItem,
        removeItem,
    }
}, {
    persist: {
        pick: ['cart'],
    },
})

export default useCartStore
