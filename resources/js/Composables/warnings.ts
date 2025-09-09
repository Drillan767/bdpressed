import { IllustrationStatus, OrderStatus } from '@/types/enums'

export interface TransitionWarning<T extends OrderStatus | IllustrationStatus> {
    status: T
    type: 'info' | 'warning' | 'destructive'
    title: string
    message: string
    requiresReason?: boolean
    reasonLabel?: string
}

const orderStateMachine = new Map<OrderStatus, TransitionWarning<OrderStatus>[]>([
    [OrderStatus.NEW, [
        {
            status: OrderStatus.PENDING_PAYMENT,
            type: 'warning',
            title: 'Envoyer le lien de paiement ?',
            message: 'Le client va recevoir un email lui indiquant qu\'il peut désormais payer sa commande. L\'action est irréversible. Continuer ?',
        },
        {
            status: OrderStatus.CANCELLED,
            type: 'destructive',
            title: 'Annuler la commande ?',
            message: 'L\'action est irréversible, et le client va recevoir un email lui informant de son annulation ainsi que la raison',
            requiresReason: true,
            reasonLabel: 'Raison de l\'annulation',
        },
    ]],
    [OrderStatus.IN_PROGRESS, [
        {
            status: OrderStatus.PENDING_PAYMENT,
            type: 'warning',
            title: 'Envoyer le lien de paiement ?',
            message: 'Le client va recevoir un email lui indiquant qu\'il peut désormais payer sa commande. L\'action est irréversible. Continuer ?',
        },
        {
            status: OrderStatus.CANCELLED,
            type: 'destructive',
            title: 'Annuler la commande ?',
            message: 'L\'action est irréversible, et le client va recevoir un email lui informant de son annulation ainsi que la raison',
            requiresReason: true,
            reasonLabel: 'Raison de l\'annulation',
        },
    ]],
    [OrderStatus.PAID, [
        {
            status: OrderStatus.DONE,
            type: 'info',
            title: 'Finaliser la commande ?',
            message: 'L\'action est irréversible. Si le client n\'a pas créé de compte, ses données seront anonymisées dans 15 jours. Confirmer ?',
        },
        {
            status: OrderStatus.CANCELLED,
            type: 'destructive',
            title: 'Annuler la commande ?',
            message: 'L\'action est irréversible, et le client va recevoir un email lui informant de son annulation ainsi que la raison. De plus, son paiment sera remboursé. Continuer ?',
            requiresReason: true,
            reasonLabel: 'Raison de l\'annulation',
        },
    ]],
    [OrderStatus.TO_SHIP, [
        {
            status: OrderStatus.SHIPPED,
            type: 'info',
            title: 'Envoyer la commande ?',
            message: 'Un email contenant le numéro de suivi va être envoyé au client. Continuer ?',
            requiresReason: true,
            reasonLabel: 'Numéro de suivi',
        },
        {
            status: OrderStatus.CANCELLED,
            type: 'destructive',
            title: 'Annuler la commande ?',
            message: 'L\'action est irréversible, et le client va recevoir un email lui informant de son annulation ainsi que la raison. De plus, son paiment sera remboursé. Continuer ?',
            requiresReason: true,
            reasonLabel: 'Raison de l\'annulation',
        },
    ]],
])

const illustrationStateMachine = new Map<IllustrationStatus, TransitionWarning<IllustrationStatus>[]>([
    [IllustrationStatus.PENDING, [
        {
            status: IllustrationStatus.DEPOSIT_PENDING,
            type: 'warning',
            title: 'Envoyer le lien de l\'Acompte ?',
            message: 'Le client va recevoir un email lui indiquant qu\'il peut désormais payer la première partie du paiement de l\'illustration. L\'action est irréversible. Continuer ?',
        },
        {
            status: IllustrationStatus.CANCELLED,
            type: 'destructive',
            title: 'Annuler l\'illustration ?',
            message: 'L\'action est irréversible, et le client va recevoir un email lui informant de son annulation ainsi que la raison',
            requiresReason: true,
            reasonLabel: 'Raison de l\'annulation',
        },
    ]],
    [IllustrationStatus.DEPOSIT_PAID, [
        {
            status: IllustrationStatus.CANCELLED,
            type: 'destructive',
            title: 'Annuler l\'illustration ?',
            message: 'L\'action est irréversible, et le client va recevoir un email lui informant de son annulation, la raison ainsi que le remboursement de l\'Acompte. Continuer ?',
            requiresReason: true,
            reasonLabel: 'Raison de l\'annulation',
        },
    ]],
    [IllustrationStatus.IN_PROGRESS, [
        {
            status: IllustrationStatus.PAYMENT_PENDING,
            type: 'warning',
            title: 'Envoyer le 2ème lien de paiement ?',
            message: 'Le client va recevoir la deuxième partie du paiement de l\'illustration. L\'action est irréversible. Continuer ?',
        },
        {
            status: IllustrationStatus.CANCELLED,
            type: 'destructive',
            title: 'Annuler l\'illustration ?',
            message: 'L\'action est irréversible, et le client va recevoir un email lui informant de son annulation, la raison ainsi que le remboursement de l\'Acompte. Continuer ?',
            requiresReason: true,
            reasonLabel: 'Raison de l\'annulation',
        },
    ]],
    [IllustrationStatus.CLIENT_REVIEW, [
        {
            status: IllustrationStatus.PAYMENT_PENDING,
            type: 'warning',
            title: 'Envoyer le 2ème lien de paiement ?',
            message: 'Le client va recevoir la deuxième partie du paiement de l\'illustration. L\'action est irréversible. Continuer ?',
        },
        {
            status: IllustrationStatus.CANCELLED,
            type: 'destructive',
            title: 'Annuler l\'illustration ?',
            message: 'L\'action est irréversible, et le client va recevoir un email lui informant de son annulation, la raison ainsi que le remboursement de l\'Acompte. Continuer ?',
            requiresReason: true,
            reasonLabel: 'Raison de l\'annulation',
        },
    ]],
])

export default function useWarnings() {
    const orderChangeWarning = (statusFrom: OrderStatus, statusTo: OrderStatus): TransitionWarning<OrderStatus> | undefined => {
        const state = orderStateMachine.get(statusFrom)
        if (!state)
            return undefined
        const transition = state.find(transition => transition.status === statusTo)
        return transition ?? undefined
    }

    const illustrationChange = (statusFrom: IllustrationStatus, statusTo: IllustrationStatus): TransitionWarning<IllustrationStatus> | undefined => {
        const state = illustrationStateMachine.get(statusFrom)
        if (!state)
            return undefined
        const transition = state.find(transition => transition.status === statusTo)
        return transition ?? undefined
    }

    return {
        orderChangeWarning,
        illustrationChange,
    }
}
