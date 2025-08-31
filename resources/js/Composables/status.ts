import { IllustrationStatus, OrderStatus, PaymentStatus, PaymentType, StatusTriggers } from '@/types/enums'

/*
interface StatusInfo {
    internal: OrderStatus | IllustrationStatus
    text: string
    color: string
}

interface TriggerInfo {
    internal: StatusTriggers
    text: string
    color: string
}

interface PaymentInfo {
    internal: PaymentType
    text: string
}
 */

interface Info<T> {
    internal: T
    text: string
    color: string
}

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
            color: '#cd0bd4',
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
    ] as const

    const illustrationStatus = [
        {
            internal: IllustrationStatus.PENDING,
            text: '🤩 Nouveau',
            color: '#4CAF50',
        },
        {
            internal: IllustrationStatus.DEPOSIT_PENDING,
            text: '💰 Accompte en attente',
            color: '#cd0bd4',
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
            internal: IllustrationStatus.PAYMENT_PENDING,
            text: '💸 En attente de paiement',
            color: '#cd0bd4',
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
    ] as const

    const paymentStatus = [
        {
            internal: PaymentStatus.PENDING,
            text: '⌚ En attente',
            color: '#FF6F00',
        },
        {
            internal: PaymentStatus.PAID,
            text: '💶 Payé',
            color: '#00C853',
        },
        {
            internal: PaymentStatus.FAILED,
            text: '❌ Échoué',
            color: '#F44336',
        },
        {
            internal: PaymentStatus.REFUNDED,
            text: '🔄 Remboursé',
            color: '#0288D1',
        },
    ] as const

    const statusTriggers = [
        {
            internal: StatusTriggers.MANUAL,
            text: '✊ Manuel',
            color: '#4CAF50',
        },
        {
            internal: StatusTriggers.WEBHOOK,
            text: '💸 Stripe',
            color: '#5167FC',
        },
        {
            internal: StatusTriggers.CUSTOMER,
            text: '👶 Client',
            color: '#FF6F00',
        },
        {
            internal: StatusTriggers.SYSTEM,
            text: '🤖 Automatique',
            color: '#454545',
        },
    ] as const

    const paymentTypes = [
        {
            internal: PaymentType.ORDER_FULL,
            text: 'Paiement de commande',
            color: '#5167FC',
        },
        {
            internal: PaymentType.ILLUSTRATION_DEPOSIT,
            text: 'Accompte illustration',
            color: '#cd0bd4',
        },
        {
            internal: PaymentType.ILLUSTRATION_FINAL,
            text: 'Paiement illustration',
            color: '#00C853',
        },
    ] as const

    // Create Maps for O(1) lookup performance
    const orderStatusMap = new Map(orderStatus.map(item => [item.internal as OrderStatus, item]))
    const illustrationStatusMap = new Map(illustrationStatus.map(item => [item.internal as IllustrationStatus, item]))
    const paymentStatusMap = new Map(paymentStatus.map(item => [item.internal as PaymentStatus, item]))
    const statusTriggersMap = new Map(statusTriggers.map(item => [item.internal as StatusTriggers, item]))
    const paymentTypeMap = new Map(paymentTypes.map(item => [item.internal as PaymentType, item]))

    function getOrderStatus(label: OrderStatus): Info<OrderStatus | IllustrationStatus> {
        const status = orderStatusMap.get(label)
        if (!status) {
            throw new Error(`Invalid order status: ${label}`)
        }
        return status
    }

    function listAvailableStatuses(states: OrderStatus[]) {
        return orderStatus.filter(item => states.includes(item.internal))
    }

    function getIllustrationStatus(label: IllustrationStatus): Info<OrderStatus | IllustrationStatus> {
        const status = illustrationStatusMap.get(label)
        if (!status) {
            throw new Error(`Invalid illustration status: ${label}`)
        }
        return status
    }

    function listIllustrationStatuses(states: IllustrationStatus[]) {
        return illustrationStatus.filter(i => states.includes(i.internal))
    }

    function listPaymentStatuses(states: PaymentStatus[]) {
        return paymentStatus.filter(i => states.includes(i.internal))
    }

    function getPaymentStatus(label: PaymentStatus) {
        const status = paymentStatusMap.get(label)
        if (!status) {
            throw new Error(`Invalid payment status: ${label}`)
        }
        return status
    }

    function listTriggers(states: StatusTriggers[]) {
        return statusTriggers.filter(i => states.includes(i.internal))
    }

    function getTrigger(label: StatusTriggers): Info<StatusTriggers> {
        const trigger = statusTriggersMap.get(label)
        if (!trigger) {
            throw new Error(`Invalid status trigger: ${label}`)
        }
        return trigger
    }

    function getPaymentType(label: PaymentType): Info<PaymentType> {
        const paymentType = paymentTypeMap.get(label)
        if (!paymentType) {
            throw new Error(`Invalid payment type: ${label}`)
        }
        return paymentType
    }

    return {
        // Order Status
        orderStatus,
        getOrderStatus,
        listAvailableStatuses,
        // Illustrations Status
        illustrationStatus,
        listIllustrationStatuses,
        getIllustrationStatus,
        // Payment Status
        paymentStatus,
        getPaymentStatus,
        listPaymentStatuses,
        // Payment Types
        paymentTypes,
        getPaymentType,
        // Triggers
        statusTriggers,
        getTrigger,
        listTriggers,
    }
}
