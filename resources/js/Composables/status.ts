enum OrderStatus {
    NEW = 'NEW',
    ILLUSTRATION_DEPOSIT_PENDING = 'ILLUSTRATION_DEPOSIT_PENDING',
    ILLUSTRATION_DEPOSIT_PAID = 'ILLUSTRATION_DEPOSIT_PAID',
    PENDING_CLIENT_REVIEW = 'PENDING_CLIENT_REVIEW',
    IN_PROGRESS = 'IN_PROGRESS',
    PAYMENT_PENDING = 'PAYMENT_PENDING',
    PAID = 'PAID',
    TO_SHIP = 'TO_SHIP',
    SHIPPED = 'SHIPPED',
    DONE = 'DONE',
    CANCELLED = 'CANCELLED',
}

export default function useStatus() {
    const status = [
        {
            internal: OrderStatus.NEW,
            text: '🤩 Nouveau',
            color: '#4CAF50',
        },
        {
            internal: OrderStatus.ILLUSTRATION_DEPOSIT_PENDING,
            text: '💰 Accompte en attente',
            color: '#FDD835',
        },
        {
            internal: OrderStatus.ILLUSTRATION_DEPOSIT_PAID,
            text: '💶 Accompte payé',
            color: '#00C853',
        },
        {
            internal: OrderStatus.PENDING_CLIENT_REVIEW,
            text: '👀 En attente de révision',
            color: '#0288D1',
        },
        {
            internal: OrderStatus.IN_PROGRESS,
            text: '✍️ En cours',
            color: '#FF6F00',
        },
        {
            internal: OrderStatus.PAYMENT_PENDING,
            text: '💸 En attente de paiement',
            color: '#FFFF00',
        },
        {
            internal: OrderStatus.PAID,
            text: '✅ Payé',
            color: '#388E3C',
        },
        {
            internal: OrderStatus.TO_SHIP,
            text: '🚚 À envoyer',
            color: '#2196F3',
        },
        {
            internal: OrderStatus.DONE,
            text: '✅ Terminé',
            color: '#4CAF50',
        },
        {
            internal: OrderStatus.CANCELLED,
            text: '❌ Annulé',
            color: '#F44336',
        },
    ]

    function getStatus(label: OrderStatus) {
        return status.find(item => item.internal === label)
    }

    return {
        getStatus,
    }
}
