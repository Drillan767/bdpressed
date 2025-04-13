<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Stripe\StripeClient;

class CopyProductsToTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stripe:copy-to-test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $testClient = new StripeClient(env('STRIPE_SECRET_KEY'));
        $liveClient = new StripeClient(env('STRIPE_LIVE_SECRET_KEY'));

        $price = $liveClient->prices->all([
            'product' => 'prod_RpKgUc401E1bMT',
            'expand' => ['data.product'],
        ]);

        $this->info(json_encode($price));
    }
}
