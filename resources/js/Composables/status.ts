import { IllustrationStatus, OrderStatus } from '@/types/enums'

export default function useStatus() {
    const orderStatus = [
        {
            internal: OrderStatus.NEW,
            text: '🤩 Nouveau',
            color: '#4CAF50',
        },
        {
            internal: OrderStatus.ILLUSTRATION_DEPOSIT_PENDING,
            text: '💰 Accompte en attente',
            color: '#00897B',
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
            color: '#7E57C2',
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

    const illustrationStatus = [
        {
            internal: IllustrationStatus.PENDING,
            text: '🤩 Nouveau',
            color: '#4CAF50',
        },
        {
            internal: IllustrationStatus.IN_PROGRESS,
            text: '✍️ En cours',
            color: '#FF6F00',
        },
        {
            internal: IllustrationStatus.DONE,
            text: '✅ Terminé',
            color: '#4CAF50',
        },
    ]

    function getOrderStatus(label: OrderStatus) {
        return orderStatus.find(item => item.internal === label)
    }

    function getIllustrationStatus(label: IllustrationStatus) {
        return illustrationStatus.find(item => item.internal === label)
    }

    return {
        orderStatus,
        getOrderStatus,
        illustrationStatus,
        getIllustrationStatus,
    }
}
