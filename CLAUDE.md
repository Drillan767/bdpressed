# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

BD Pressed is a Laravel-based e-commerce platform for custom comic book illustrations. It features a dual frontend (Vue.js with Vuetify) and backend (Laravel) architecture using Inertia.js for seamless integration.

## Development Commands

### Backend (Laravel with Sail)
- `./vendor/bin/sail up` - Start Docker development environment
- `./vendor/bin/sail artisan migrate` - Run database migrations
- `./vendor/bin/sail artisan db:seed` - Seed database
- `./vendor/bin/sail test` - Run PHPUnit/Pest tests
- `./vendor/bin/sail artisan pint` - Format PHP code
- `composer dev` - Start full development stack (server, queue, logs, vite)

### Frontend (Node.js/Vue.js)
- `yarn dev` - Start Vite development server
- `yarn build` - Build for production (includes TypeScript compilation)
- `yarn lint` - Run ESLint
- `yarn lint:fix` - Auto-fix ESLint issues

## Architecture

### Backend Structure
- **Models**: Core entities (Order, Illustration, Product, User, Comic)
- **State Machines**: Business logic for Order and Illustration workflows
- **Services**: OrderService, StripeService, IllustrationService for complex operations
- **Settings**: Spatie Settings for configurable application settings
- **Observers**: ProductObserver, IllustrationPriceObserver for model event handling

### Frontend Structure
- **Vue 3 + TypeScript** with Composition API
- **Vuetify 3** for UI components
- **Inertia.js** for SPA-like experience without API complexity
- **Pinia** stores for state management (cartStore, illustrationStore)
- **Vee-Validate** for form validation

### Key Business Logic
The application uses state machines for order and illustration workflows:
- **Order Flow**: NEW → IN_PROGRESS → PENDING_PAYMENT → PAID → TO_SHIP → SHIPPED → DONE
- **Illustration Flow**: PENDING → DEPOSIT_PENDING → DEPOSIT_PAID → IN_PROGRESS → CLIENT_REVIEW → PAYMENT_PENDING → COMPLETED

State transitions are managed by dedicated StateMachine classes with validation and automatic Stripe webhook integration.
A file describing the workflow is available at `docs/STATE_MACHINES.md`

## Testing

Uses **Pest PHP** for backend testing:
- `./vendor/bin/sail test` - Run all tests
- `./vendor/bin/sail test --filter=FeatureName` - Run specific test

## Key Configuration

- **TypeScript**: Strict mode enabled with path aliases (`@/` → `resources/js/`)
- **ESLint**: Uses @antfu/eslint-config with 4-space indentation and single quotes
- **Vite**: Configured for Laravel integration with SSR support
- **Docker**: Full development environment via Laravel Sail

## Special Features

- **Holiday Mode**: Middleware to temporarily disable shopping features
- **Custom Illustrations**: Complex workflow with deposits, reviews, and final payments
- **Stripe Integration**: Webhook-driven payment processing with estimated vs actual fee handling
- **Role-based Access**: Admin and user roles with different interfaces

## Stripe Fee Management

The application handles Stripe processing fees in two phases:

1. **Estimated Fees** (Order Creation): When orders are created, Stripe fees are estimated using `StripeService::calculateStripeFee()` based on EU rates (1.5% + €0.25) or UK rates (2.5% + €0.25)

2. **Actual Fees** (After Payment): Once payment is processed via webhook, the actual Stripe fee from the payment intent is stored in `OrderPayment.stripe_fee` and becomes the definitive reference

**Key Points:**
- Email notifications and fee displays should use actual fees when available, estimated fees otherwise
- `OrderService::calculateFees()` automatically handles this logic
- Estimated fees are used for order confirmations, actual fees for payment confirmations
