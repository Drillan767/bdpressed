import type { CartIllustration, CartItem } from '@/types'
import useToast from '@/Composables/toast'
import { defineStore } from 'pinia'
import { computed, ref } from 'vue'

const useCartStore = defineStore('cart', () => {
    const { showError } = useToast()
    const cart = ref<CartItem[]>([])

    function addItem(item: Omit<(CartIllustration | CartItem), 'quantity'>) {
        const itemIndex = cart.value.findIndex(cartItem => cartItem.id === item.id)

        if (itemIndex > -1) {
            if (cart.value[itemIndex].stock === 0) {
                showError('J\'ai plus de stock 🥲')
                return
            }

            cart.value[itemIndex].quantity++
            cart.value[itemIndex].stock--
        }
        else {
            cart.value.push({
                ...item,
                quantity: 1,
            })
        }
    }

    const totalPrice = computed(() => {
        return cart.value.reduce((acc, item) => acc + item.price * item.quantity, 0)
    })

    const totalWeight = computed(() => {
        return cart.value.reduce((acc, item) => acc + item.weight * item.quantity, 0)
    })

    const tax = computed(() => {
        const tax = totalPrice.value * 0.015 + 0.25
        return Number(tax.toFixed(2))
    })

    function handleQuantity(index: number, action: 'increase' | 'decrease') {
        if (action === 'increase') {
            if (cart.value[index].stock === 0) {
                showError('J\'ai plus de stock 🥲')
                return
            }
            cart.value[index].quantity++
            cart.value[index].stock--
        }
        else {
            if (cart.value[index].quantity > 1) {
                cart.value[index].quantity--
                cart.value[index].stock++
            }
            else {
                removeItem(cart.value[index])
            }
        }
    }

    function removeItem(item: CartItem) {
        cart.value.splice(cart.value.indexOf(item), 1)
    }

    return {
        cart,
        totalPrice,
        totalWeight,
        tax,
        addItem,
        handleQuantity,
        removeItem,
    }
}, {
    persist: {
        pick: ['cart'],
    },
})

export default useCartStore
