<?php

namespace App\Services;

use App\Models\IllustrationPrice;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Stripe\StripeClient;

class StripeService
{
    private StripeClient $client {
        get {
            return $this->client;
        }
    }

    public function __construct()
    {
        $this->client = new StripeClient(config('app.stripe.secret_key'));
    }

    public function getClient(): StripeClient
    {
        return $this->client;
    }

    /**
     * Retrieve a product from Stripe by ID
     */
    public function retrieveProduct(?string $stripeProductId)
    {
        if ($stripeProductId) {
            try {
                return $this->client->products->retrieve($stripeProductId);
            } catch (\Exception $e) {
                Log::error('Error retrieving Stripe product: '.$e->getMessage());

                return null;
            }
        } else {
            return $this->client->products->all();
        }
    }

    /**
     * Get product images for Stripe
     */
    private function getProductImages(Product $product): array
    {
        $images = [];

        // Add a promoted image if it exists
        if (! empty($product->promotedImage)) {
            $images[] = URL::to($product->promotedImage);
        }

        // Add illustrations if they exist
        if (! empty($product->illustrations)) {
            foreach ($product->illustrations as $illustration) {
                if (isset($illustration['path'])) {
                    $images[] = URL::to($illustration['path']);
                }
            }
        }

        return $images;
    }

    /**
     * Create a product in Stripe
     */
    public function createProduct(Product $product)
    {
        try {
            $productData = [
                'name' => $product->name,
                'description' => $product->description,
                'metadata' => [
                    'product_id' => $product->id,
                    'slug' => $product->slug,
                ],
            ];

            // Add images if available
            $images = $this->getProductImages($product);
            if (! empty($images)) {
                $productData['images'] = $images;
            }

            $stripeProduct = $this->client->products->create($productData);

            // Create a price for the product
            $this->client->prices->create([
                'product' => $stripeProduct->id,
                'unit_amount' => $product->price->cents(),
                'currency' => 'eur',
            ]);

            // Store the Stripe product ID in the product model
            $product->stripe_link = $stripeProduct->id;
            $product->save(['timestamps' => false]);

            return $stripeProduct;
        } catch (\Exception $e) {
            Log::error('Error creating Stripe product: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Update a product in Stripe
     */
    public function updateProduct(Product $product)
    {
        if (empty($product->stripe_link)) {
            return $this->createProduct($product);
        }

        try {
            $productData = [
                'name' => $product->name,
                'description' => $product->description,
                'metadata' => [
                    'product_id' => $product->id,
                    'slug' => $product->slug,
                ],
            ];

            // Add images if available
            $images = $this->getProductImages($product);
            if (! empty($images)) {
                $productData['images'] = $images;
            }

            $stripeProduct = $this->client->products->update(
                $product->stripe_link,
                $productData
            );

            // Update the price if needed
            $this->updateProductPrice($product);

            return $stripeProduct;
        } catch (\Exception $e) {
            Log::error('Error updating Stripe product: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Update the price of a product in Stripe
     */
    private function updateProductPrice(Product $product)
    {
        try {
            // Get all active prices for the product
            $prices = $this->client->prices->all([
                'product' => $product->stripe_link,
                'active' => true,
            ]);

            // If there are active prices, check if the price has changed
            if (! empty($prices->data)) {
                $currentPrice = $prices->data[0];
                $currentAmountCents = $currentPrice->unit_amount; // Already in cents

                // Only update if the price has changed (compare cents)
                if ($currentAmountCents !== $product->price->cents()) {

                    // Archive the current price
                    $this->client->prices->update($currentPrice->id, ['active' => false]);

                    // Create a new price
                    $newPrice = $this->client->prices->create([
                        'product' => $product->stripe_link,
                        'unit_amount' => $product->price->cents(),
                        'currency' => 'eur',
                    ]);

                }
            } else {
                // No active prices found, create a new one
                $this->client->prices->create([
                    'product' => $product->stripe_link,
                    'unit_amount' => $product->price->cents(),
                    'currency' => 'eur',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error updating product price in Stripe: '.$e->getMessage());
        }
    }

    /**
     * Delete a product from Stripe
     */
    public function deleteProduct(Product $product)
    {
        if (empty($product->stripe_link)) {
            return null;
        }

        try {
            // Archive all prices associated with the product
            $prices = $this->client->prices->all(['product' => $product->stripe_link]);
            foreach ($prices->data as $price) {
                $this->client->prices->update($price->id, ['active' => false]);
            }

            // Delete the product
            return $this->client->products->delete($product->stripe_link);
        } catch (\Exception $e) {
            Log::error('Error deleting Stripe product: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Create a Stripe product for an illustration price
     */
    public function createIllustrationPriceProduct(IllustrationPrice $illustrationPrice)
    {
        try {
            $productData = [
                'name' => $illustrationPrice->name,
                'description' => "Illustration price configuration for {$illustrationPrice->name}",
                'metadata' => [
                    'price_key' => $illustrationPrice->key,
                    'category' => explode('_', $illustrationPrice->key)[0] ?? 'default',
                    'type' => 'illustration_price',
                ],
            ];

            return $this->client->products->create($productData);
        } catch (\Exception $e) {
            Log::error('Error creating Stripe product for illustration price: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a Stripe price for an illustration price product
     */
    public function createIllustrationPrice(string $productId, float $amount)
    {
        try {
            return $this->client->prices->create([
                'product' => $productId,
                'unit_amount' => $amount, // Already in cents
                'currency' => 'eur',
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating Stripe price for illustration price: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Handle all Stripe operations for a new illustration price
     */
    public function handleIllustrationPriceCreation(IllustrationPrice $illustrationPrice): bool
    {
        try {
            // Create the product in Stripe
            $productData = [
                'name' => "$illustrationPrice->category - $illustrationPrice->name",
                'description' => "Illustration price configuration for {$illustrationPrice->name}",
                'metadata' => [
                    'price_key' => $illustrationPrice->key,
                    'category' => explode('_', $illustrationPrice->key)[0] ?? 'default',
                    'type' => 'illustration_price',
                ],
            ];

            $stripeProduct = $this->client->products->create($productData);
            Log::info('Stripe product created:', ['id' => $stripeProduct->id]);

            // Create the price in Stripe
            $stripePrice = $this->client->prices->create([
                'product' => $stripeProduct->id,
                'unit_amount' => $illustrationPrice->price->cents(),
                'currency' => 'eur',
            ]);
            Log::info('Stripe price created:', ['id' => $stripePrice->id]);

            // Update the model with Stripe IDs
            $illustrationPrice->stripe_product_id = $stripeProduct->id;
            $illustrationPrice->stripe_price_id = $stripePrice->id;

            return $illustrationPrice->saveQuietly();
        } catch (\Exception $e) {
            Log::error('Error handling Stripe operations for illustration price: '.$e->getMessage(), [
                'illustration_price_id' => $illustrationPrice->id ?? null,
                'exception' => $e,
            ]);

            return false;
        }
    }

    /**
     * Handle all Stripe operations for updating an illustration price
     */
    public function handleIllustrationPriceUpdate(IllustrationPrice $illustrationPrice): bool
    {
        try {
            if ($illustrationPrice->stripe_product_id) {
                // Update the product in Stripe
                $this->client->products->update(
                    $illustrationPrice->stripe_product_id,
                    [
                        'name' => "$illustrationPrice->category - $illustrationPrice->name",
                        'metadata' => [
                            'price_key' => $illustrationPrice->key,
                            'category' => explode('_', $illustrationPrice->key)[0] ?? 'default',
                            'type' => 'illustration_price',
                        ],
                    ]
                );

                // If price changed, create new price and archive old one
                if ($illustrationPrice->wasChanged('price')) {
                    // Archive old price
                    if ($illustrationPrice->stripe_price_id) {
                        $this->client->prices->update(
                            $illustrationPrice->stripe_price_id,
                            ['active' => false]
                        );
                    }

                    // Create new price
                    $newPrice = $this->client->prices->create([
                        'product' => $illustrationPrice->stripe_product_id,
                        'unit_amount' => $illustrationPrice->price->cents(),
                        'currency' => 'eur',
                    ]);

                    // Update model with new price ID
                    $illustrationPrice->stripe_price_id = $newPrice->id;

                    return $illustrationPrice->saveQuietly();
                }
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Error handling Stripe update for illustration price: '.$e->getMessage(), [
                'illustration_price_id' => $illustrationPrice->id ?? null,
                'exception' => $e,
            ]);

            return false;
        }
    }

    /**
     * Handle all Stripe operations for deleting an illustration price
     */
    public function handleIllustrationPriceDeletion(IllustrationPrice $illustrationPrice): bool
    {
        try {
            if ($illustrationPrice->stripe_product_id) {
                // Archive the price
                if ($illustrationPrice->stripe_price_id) {
                    $this->client->prices->update(
                        $illustrationPrice->stripe_price_id,
                        ['active' => false]
                    );
                }

                // Archive the product
                $this->client->products->update(
                    $illustrationPrice->stripe_product_id,
                    ['active' => false]
                );
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Error handling Stripe deletion for illustration price: '.$e->getMessage(), [
                'illustration_price_id' => $illustrationPrice->id ?? null,
                'exception' => $e,
            ]);

            return false;
        }
    }

    /**
     * Create a Stripe payment link for an order
     * Note: This method should be called after OrderPayment record is created
     */
    public function createPaymentLink(Order $order): ?string
    {
        try {
            $lineItems = [];
            $shippingRate = null;

            // Add products from order details
            foreach ($order->details as $detail) {
                $product = $detail->product;

                // Get an active price for this product
                $prices = $this->client->prices->all([
                    'product' => $product->stripe_link,
                    'active' => true,
                ]);

                if (! empty($prices->data)) {
                    $lineItems[] = [
                        'price' => $prices->data[0]->id,
                        'quantity' => $detail->quantity,
                    ];
                }
            }

            $totalFees = $order->shipmentFees->cents();
            if ($totalFees > 0) {
                // Create a one-time product for shipping and fees
                $shippingRate = $this->client->shippingRates->create([
                    'display_name' => 'Frais de port',
                    'type' => 'fixed_amount',
                    'fixed_amount' => [
                        'amount' => $totalFees,
                        'currency' => 'eur',
                    ],
                ]);
            }

            // Create the payment link
            $paymentLink = $this->client->paymentLinks->create([
                'line_items' => $lineItems,
                'metadata' => [
                    'order_id' => $order->id,
                    'order_reference' => $order->reference,
                ],
                'shipping_options' => [['shipping_rate' => $shippingRate]],
                'after_completion' => [
                    'type' => 'redirect',
                    'redirect' => [
                        'url' => route('payment.success'),
                    ],
                ],
            ]);

            return $paymentLink->url;

        } catch (\Exception $e) {
            Log::error('Error creating Stripe payment link: '.$e->getMessage(), [
                'order_id' => $order->id,
                'exception' => $e,
            ]);

            return null;
        }
    }

    /**
     * Calculate Stripe processing fees for a given amount
     * EU rates: 1.5% + €0.25 (standard), 2.5% + €0.25 (UK/non-EU)
     */
    public function calculateStripeFee(int $amountCents, string $region = 'EU'): int
    {
        $percentageFee = match ($region) {
            'UK' => 0.025, // 2.5%
            'EU' => 0.015, // 1.5%
            default => 0.015 // Default to EU rate
        };

        $fixedFee = 25; // €0.25 in cents

        return intval($amountCents * $percentageFee) + $fixedFee;
    }

    /**
     * Update OrderPayment with payment intent ID from the payment link
     */
    public function updatePaymentWithIntent(OrderPayment $payment, string $paymentLinkUrl): void
    {
        try {
            // Extract payment link ID from URL
            $paymentLinkId = basename(parse_url($paymentLinkUrl, PHP_URL_PATH));

            // Get the payment link to find associated payment intent
            $paymentLink = $this->client->paymentLinks->retrieve($paymentLinkId);

            // Note: Payment intent is created when customer starts checkout
            // We'll update this in the webhook when payment_intent.succeeded fires

        } catch (\Exception $e) {
            Log::error('Error updating payment with intent: '.$e->getMessage(), [
                'payment_id' => $payment->id,
                'payment_link_url' => $paymentLinkUrl,
            ]);
        }
    }

    /**
     * Create a Stripe payment link for an illustration payment (deposit or final)
     */
    public function createIllustrationPaymentLink(OrderPayment $payment): ?string
    {
        try {
            $illustration = $payment->illustration;
            $order = $payment->order ?? $illustration->order;

            if (! $illustration || ! $order) {
                Log::error('Missing illustration or order for payment link creation', [
                    'payment_id' => $payment->id,
                    'illustration_id' => $payment->illustration_id,
                    'order_id' => $payment->order_id,
                ]);

                return null;
            }

            // Create a one-time product for this specific payment
            $productName = $payment->type->value === 'illustration_deposit'
                ? "Deposit - Custom Illustration #{$illustration->id}"
                : "Final Payment - Custom Illustration #{$illustration->id}";

            $product = $this->client->products->create([
                'name' => $productName,
                'description' => $payment->description ?? 'Payment for custom illustration',
                'metadata' => [
                    'illustration_id' => $illustration->id,
                    'order_id' => $order->id,
                    'payment_type' => $payment->type->value,
                ],
            ]);

            // Create the price
            $price = $this->client->prices->create([
                'product' => $product->id,
                'unit_amount' => $payment->amount->cents(),
                'currency' => 'eur',
            ]);

            // Create the payment link
            $paymentLink = $this->client->paymentLinks->create([
                'line_items' => [
                    [
                        'price' => $price->id,
                        'quantity' => 1,
                    ],
                ],
                'metadata' => [
                    'illustration_id' => $illustration->id,
                    'order_id' => $order->id,
                    'payment_id' => $payment->id,
                    'payment_type' => $payment->type->value,
                ],
                'after_completion' => [
                    'type' => 'redirect',
                    'redirect' => [
                        'url' => route('payment.success'),
                    ],
                ],
            ]);

            return $paymentLink->url;

        } catch (\Exception $e) {
            Log::error('Error creating Stripe payment link for illustration: '.$e->getMessage(), [
                'payment_id' => $payment->id,
                'illustration_id' => $payment->illustration_id,
                'exception' => $e,
            ]);

            return null;
        }
    }

    /**
     * Retrieve Stripe fee from a balance transaction
     */
    public function getStripeFeeFromPaymentIntent(array $paymentIntent): ?int
    {
        // Check if charges exist and have the balance transaction
        if (! isset($paymentIntent['charges']['data'][0]['balance_transaction'])) {
            return null;
        }

        try {
            $balanceTransactionId = $paymentIntent['charges']['data'][0]['balance_transaction'];
            $balanceTransaction = $this->client->balanceTransactions->retrieve($balanceTransactionId);

            return $balanceTransaction->fee;
        } catch (\Exception $e) {
            Log::warning('Failed to retrieve Stripe fee from balance transaction', [
                'balance_transaction_id' => $balanceTransactionId ?? 'missing',
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Create a refund for a payment intent
     * Reason is required for audit trail
     */
    public function refundPayment(string $paymentIntentId, int $amount, string $reason): array
    {
        try {
            $refundData = [
                'payment_intent' => $paymentIntentId,
                'amount' => $amount,
                'reason' => $reason,
            ];

            $refund = $this->client->refunds->create($refundData);

            return [
                'success' => true,
                'refund' => $refund,
                'refund_id' => $refund->id,
                'amount' => $refund->amount,
                'status' => $refund->status,
            ];

        } catch (\Exception $e) {
            Log::error('Error creating Stripe refund: '.$e->getMessage(), [
                'payment_intent_id' => $paymentIntentId,
                'amount' => $amount,
                'reason' => $reason,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get refunds for a payment intent
     */
    public function getRefunds(string $paymentIntentId): array
    {
        try {
            $refunds = $this->client->refunds->all([
                'payment_intent' => $paymentIntentId,
            ]);

            return [
                'success' => true,
                'refunds' => $refunds->data,
            ];

        } catch (\Exception $e) {
            Log::error('Error retrieving Stripe refunds: '.$e->getMessage(), [
                'payment_intent_id' => $paymentIntentId,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
