import { IllustrationStatus, OrderStatus } from '@/types/enums'

export default function useStatus() {
    const orderStatus = [
        {
            internal: OrderStatus.NEW,
            text: '🤩 Nouveau',
            color: '#4CAF50',
        },
        {
            internal: OrderStatus.IN_PROGRESS,
            text: '✍️ En cours',
            color: '#FF6F00',
        },
        {
            internal: OrderStatus.PENDING_PAYMENT,
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
            internal: OrderStatus.SHIPPED,
            text: '🛫 Envoyé',
            color: '#0065ff',
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
            internal: IllustrationStatus.DEPOSIT_PENDING,
            text: '💰 Accompte en attente',
            color: '#FDD835',
        },
        {
            internal: IllustrationStatus.DEPOSIT_PAID,
            text: '💶 Accompte payé',
            color: '#00C853',
        },
        {
            internal: IllustrationStatus.CLIENT_REVIEW,
            text: '👀 En attente de révision',
            color: '#0288D1',
        },
        {
            internal: IllustrationStatus.IN_PROGRESS,
            text: '✍️ En cours',
            color: '#FF6F00',
        },
        {
            internal: IllustrationStatus.COMPLETED,
            text: '✅ Terminé',
            color: '#4CAF50',
        },
        {
            internal: IllustrationStatus.CANCELLED,
            text: '❌ Annulé',
            color: '#F44336',
        },
    ]

    function getOrderStatus(label: OrderStatus) {
        return orderStatus.find(item => item.internal === label)
    }

    function listAvailableStatuses(states: OrderStatus[]) {
        return orderStatus.filter(item => states.includes(item.internal))
    }

    function getIllustrationStatus(label: IllustrationStatus) {
        return illustrationStatus.find(item => item.internal === label)
    }

    function listIllustrationStatuses(states: IllustrationStatus[]) {
        return illustrationStatus.filter(i => states.includes(i.internal))
    }

    return {
        orderStatus,
        getOrderStatus,
        listAvailableStatuses,
        illustrationStatus,
        listIllustrationStatuses,
        getIllustrationStatus,
    }
}
