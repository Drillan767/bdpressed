export default function useNumbers() {
    function formatPrice(price: number) {
        return price.toLocaleString('fr-FR', {
            style: 'currency',
            currency: 'EUR',
        })
    }

    return {
        formatPrice,
    }
}
