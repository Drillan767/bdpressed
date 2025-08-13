# State Machine Documentation

This document describes the state machine workflows for Orders and Illustrations in the BD Pressed e-commerce system.

## Overview

The system uses two separate state machines:
1. **Order State Machine**: Manages the overall order lifecycle (acceptance, payment, shipping)
2. **Illustration State Machine**: Manages the custom illustration workflow (deposit, work, final payment)

## Order State Machine

### States
- `NEW`: Initial state when order is created
- `IN_PROGRESS`: Artist has accepted the order / regular items are being processed
- `PENDING_PAYMENT`: Waiting for customer payment (for immediate payment items)
- `PAID`: Payment received for order
- `TO_SHIP`: Order ready for shipping
- `SHIPPED`: Order has been shipped with tracking
- `DONE`: Order completed and delivered
- `CANCELLED`: Order cancelled

### Transitions
```
NEW
├── IN_PROGRESS (artist accepts order/regular items)
├── PENDING_PAYMENT (for immediate payment items)
└── CANCELLED

PENDING_PAYMENT
└── PAID (webhook handles payment)

IN_PROGRESS
└── PAID (when all work completed)

PAID
├── TO_SHIP
└── CANCELLED (trigger Stripe refund)

TO_SHIP
├── SHIPPED (add tracking number)
└── CANCELLED (trigger Stripe refund)

SHIPPED
└── DONE

CANCELLED (terminal state)
```

### Business Rules
- Orders with mixed content (regular items + illustrations) move to `TO_SHIP` only when ALL shippable items are ready:
  - Regular items: Ready immediately after `PAID`
  - Illustrations with `print=true`: Ready when illustration status is `COMPLETED`
  - Illustrations with `print=false`: Ignored for shipping (digital only)

## Illustration State Machine

### States
- `PENDING`: Initial state when illustration is ordered
- `DEPOSIT_PENDING`: Artist has accepted the job, waiting for deposit
- `DEPOSIT_PAID`: Deposit payment received
- `IN_PROGRESS`: Artist is working on the illustration
- `CLIENT_REVIEW`: Illustration ready for client review
- `PAYMENT_PENDING`: Client approved, waiting for final payment
- `COMPLETED`: Final payment received, work finished
- `CANCELLED`: Illustration cancelled

### Transitions
```
PENDING
├── DEPOSIT_PENDING (artist accepts job, triggers first payment)
└── CANCELLED

DEPOSIT_PENDING
└── DEPOSIT_PAID (webhook handles deposit payment)

DEPOSIT_PAID
├── IN_PROGRESS
└── CANCELLED

IN_PROGRESS
├── CLIENT_REVIEW
├── PAYMENT_PENDING (skip review, go straight to final payment)
└── CANCELLED

CLIENT_REVIEW
├── IN_PROGRESS (client requests changes - unlimited transitions allowed)
├── PAYMENT_PENDING (client approves, triggers second payment - POINT OF NO RETURN)
└── CANCELLED (artist's discretion)

PAYMENT_PENDING
└── COMPLETED (webhook handles final payment)

CANCELLED (terminal state)
```

## Refund Policy

### Illustration Orders
- **Before CLIENT_REVIEW**: Full refund possible (including deposit)
- **CLIENT_REVIEW → PAYMENT_PENDING**: Point of no return - no refunds after client approval
- **During CLIENT_REVIEW**: Artist has full discretion to handle difficult clients
- **Unlimited revisions**: No limit on CLIENT_REVIEW ↔ IN_PROGRESS transitions

### Cancellation Rules
- Cancellation from `PAYMENT_PENDING` or `COMPLETED`: No automatic refund (requires manual intervention)
- Cancellation from earlier states: Automatic refund processing
- Artist can use discretion to manage client relationships and revision requests

## Webhook Integration

The following state transitions are handled automatically by Stripe webhooks:
- `PENDING_PAYMENT` → `PAID` (order payment)
- `DEPOSIT_PENDING` → `DEPOSIT_PAID` (illustration deposit)
- `PAYMENT_PENDING` → `COMPLETED` (illustration final payment)

## UI Considerations

### Warnings/Confirmations Required
- Any transition to `CANCELLED`: Display warning, optionally collect reason
- `TO_SHIP` → `DONE`: Display warning if customer is a guest (no account for tracking)
- `PAID` → `CANCELLED`: Automatically trigger Stripe refund
- `TO_SHIP` → `CANCELLED`: Automatically trigger Stripe refund

### State-Dependent Actions
- `TO_SHIP`: Require tracking number input
- `DEPOSIT_PENDING`: Generate and display Stripe payment link for deposit
- `PAYMENT_PENDING`: Generate and display Stripe payment link for final payment

## Database Schema Notes

### Order Model
- `status` field uses `OrderStatus` enum
- Stripe payment links stored in `stripe_payment_link` field

### Illustration Model  
- `status` field should use `IllustrationStatus` enum (to be created)
- `print` boolean field determines if illustration needs shipping
- Related to Order via `order_id` foreign key

## Implementation Notes

- State transitions should be validated before execution
- Failed transitions should throw `InvalidStateTransitionException`
- State changes should be logged for audit trail
- Webhook handlers should be idempotent to handle duplicate events