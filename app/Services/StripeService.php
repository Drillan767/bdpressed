<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class StripeService
{
    private StripeClient $client;

    public function __construct()
    {
        $this->client = new StripeClient(env('STRIPE_SECRET_KEY'));
    }

    public function retrieveProduct()
    {
        $allProducts = $this->client->products->all();
        Log::info(json_encode($allProducts->data));
    }
}