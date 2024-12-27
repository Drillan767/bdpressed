import type { CartItem } from '@/types'
import { defineStore } from 'pinia'
import { computed, ref } from 'vue'

const useCartStore = defineStore('cart', () => {
    const cart = ref<CartItem[]>([])

    function addItem(item: Omit<CartItem, 'quantity'>) {
        const itemIndex = cart.value.findIndex(cartItem => cartItem.id === item.id)

        if (itemIndex > -1) {
            cart.value[itemIndex].quantity++
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
        return totalPrice.value * 0.015 + 0.25
    })

    function handleQuantity(index: number, action: 'increase' | 'decrease') {
        if (action === 'increase') {
            cart.value[index].quantity++
        }
        else {
            if (cart.value[index].quantity > 1) {
                cart.value[index].quantity--
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
