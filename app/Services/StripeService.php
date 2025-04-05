<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;
use Illuminate\Support\Facades\URL;

class StripeService
{
    private StripeClient $client;

    public function __construct()
    {
        $this->client = new StripeClient(env('STRIPE_SECRET_KEY'));
    }

    /**
     * Retrieve a product from Stripe by ID
     */
    public function retrieveProduct(string $stripeProductId = null)
    {
        if ($stripeProductId) {
            try {
                $product = $this->client->products->retrieve($stripeProductId);
                Log::info('Retrieved Stripe product: ' . json_encode($product));
                return $product;
            } catch (\Exception $e) {
                Log::error('Error retrieving Stripe product: ' . $e->getMessage());
                return null;
            }
        } else {
            $allProducts = $this->client->products->all();
            Log::info('Retrieved all Stripe products: ' . json_encode($allProducts->data));
            return $allProducts;
        }
    }

    /**
     * Get product images for Stripe
     */
    private function getProductImages(Product $product): array
    {
        $images = [];
        
        // Add promoted image if it exists
        if (!empty($product->promotedImage)) {
            $images[] = URL::to($product->promotedImage);
        }
        
        // Add illustrations if they exist
        if (!empty($product->illustrations)) {
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
            if (!empty($images)) {
                $productData['images'] = $images;
            }
            
            $stripeProduct = $this->client->products->create($productData);

            // Create a price for the product
            $this->client->prices->create([
                'product' => $stripeProduct->id,
                'unit_amount' => (int)($product->price * 100), // Convert to cents
                'currency' => 'eur',
            ]);

            // Store the Stripe product ID in the product model
            $product->stripe_link = $stripeProduct->id;
            $product->save(['timestamps' => false]);

            Log::info('Created Stripe product: ' . json_encode($stripeProduct));
            return $stripeProduct;
        } catch (\Exception $e) {
            Log::error('Error creating Stripe product: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Update a product in Stripe
     */
    public function updateProduct(Product $product)
    {
        if (empty($product->stripe_link)) {
            Log::warning('Product does not have a Stripe ID, creating new product');
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
            if (!empty($images)) {
                $productData['images'] = $images;
            }
            
            $stripeProduct = $this->client->products->update(
                $product->stripe_link,
                $productData
            );

            // Update the price if needed
            $this->updateProductPrice($product);

            Log::info('Updated Stripe product: ' . json_encode($stripeProduct));
            return $stripeProduct;
        } catch (\Exception $e) {
            Log::error('Error updating Stripe product: ' . $e->getMessage());
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
                'active' => true
            ]);

            // If there are active prices, check if the price has changed
            if (!empty($prices->data)) {
                $currentPrice = $prices->data[0];
                $currentAmount = $currentPrice->unit_amount / 100; // Convert from cents to euros
                
                // Only update if the price has changed
                if (abs($currentAmount - $product->price) > 0.01) { // Use a small epsilon for float comparison
                    Log::info("Price changed from {$currentAmount} to {$product->price}, updating in Stripe");
                    
                    // Archive the current price
                    $this->client->prices->update($currentPrice->id, ['active' => false]);
                    
                    // Create a new price
                    $newPrice = $this->client->prices->create([
                        'product' => $product->stripe_link,
                        'unit_amount' => (int)($product->price * 100), // Convert to cents
                        'currency' => 'eur',
                    ]);
                    
                    Log::info('Created new price in Stripe: ' . json_encode($newPrice));
                } else {
                    Log::info("Price unchanged ({$currentAmount}), no update needed");
                }
            } else {
                // No active prices found, create a new one
                Log::info("No active prices found for product, creating new price");
                
                $newPrice = $this->client->prices->create([
                    'product' => $product->stripe_link,
                    'unit_amount' => (int)($product->price * 100), // Convert to cents
                    'currency' => 'eur',
                ]);
                
                Log::info('Created new price in Stripe: ' . json_encode($newPrice));
            }
        } catch (\Exception $e) {
            Log::error('Error updating product price in Stripe: ' . $e->getMessage());
        }
    }

    /**
     * Delete a product from Stripe
     */
    public function deleteProduct(Product $product)
    {
        if (empty($product->stripe_link)) {
            Log::warning('Product does not have a Stripe ID, nothing to delete');
            return null;
        }

        try {
            // Archive all prices associated with the product
            $prices = $this->client->prices->all(['product' => $product->stripe_link]);
            foreach ($prices->data as $price) {
                $this->client->prices->update($price->id, ['active' => false]);
            }

            // Delete the product
            $deletedProduct = $this->client->products->delete($product->stripe_link);
            Log::info('Deleted Stripe product: ' . json_encode($deletedProduct));
            return $deletedProduct;
        } catch (\Exception $e) {
            Log::error('Error deleting Stripe product: ' . $e->getMessage());
            return null;
        }
    }
}