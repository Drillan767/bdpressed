<?php

namespace App\Console\Commands;

use App\Enums\IllustrationStatus;
use App\Enums\OrderStatus;
use App\Models\Illustration;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderPayment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Console\Command;

class GenerateFakeDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fake:generate
                            {--users=50 : Number of users to create}
                            {--products=15 : Number of products to create}
                            {--orders=* : Maximum orders per user (random between 0 and this)}
                            {--illustrations=30 : Number of illustrations to create}
                            {--clean : Clean existing fake data before generating}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate fake data for statistics and testing purposes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->error('This command can only be run in local or testing environments.');

            return 1;
        }

        if ($this->option('clean')) {
            $this->cleanFakeData();
        }

        $this->info('🚀 Starting fake data generation...');

        $userCount = $this->option('users');
        $productCount = $this->option('products');
        $maxOrdersPerUser = $this->option('orders') ?: 5;
        $illustrationCount = $this->option('illustrations');

        // Generate users
        $this->info("👥 Creating {$userCount} users...");
        $users = User::factory()
            ->count($userCount)
            ->afterCreating(function ($user) {
                $user->assignRole('user');
            })
            ->create();

        $this->info("✅ Created {$users->count()} users");

        // Generate products
        $this->info("📦 Creating {$productCount} products...");
        $products = Product::factory()
            ->count($productCount)
            ->createQuietly();

        $this->info("✅ Created {$products->count()} products");

        // Generate orders with realistic distribution
        $this->info('🛒 Creating orders...');
        $totalOrders = 0;
        $orderStatuses = OrderStatus::cases();

        $progressBar = $this->output->createProgressBar($users->count());
        $progressBar->start();

        foreach ($users as $user) {
            // Random number of orders per user (weighted towards fewer orders)
            $orderCount = $this->getRandomOrderCount($maxOrdersPerUser);

            for ($i = 0; $i < $orderCount; $i++) {
                $createdAt = fake()->dateTimeBetween('-1 year', 'now');

                // Status distribution: more new/paid orders, fewer cancelled
                $status = $this->getWeightedOrderStatus($orderStatuses);

                $order = Order::factory()
                    ->forUser($user)
                    ->withStatus($status)
                    ->create([
                        'created_at' => $createdAt,
                        'updated_at' => fake()->dateTimeBetween($createdAt, 'now'),
                    ]);

                // Add order details (1-4 products per order)
                $this->addOrderDetails($order, $products);

                // Add payments for completed orders
                if ($this->shouldHavePayment($status)) {
                    OrderPayment::factory()
                        ->create([
                            'order_id' => $order->id,
                            'created_at' => $createdAt,
                        ]);
                }

                $totalOrders++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        $this->info("✅ Created {$totalOrders} orders");

        // Generate illustrations
        $this->info("🎨 Creating {$illustrationCount} illustrations...");
        $ordersForIllustrations = Order::inRandomOrder()->limit($illustrationCount)->get();
        $illustrationStatuses = IllustrationStatus::cases();

        foreach ($ordersForIllustrations as $order) {
            $status = fake()->randomElement($illustrationStatuses);

            Illustration::factory()
                ->forOrder($order)
                ->withStatus($status)
                ->create([
                    'created_at' => $order->created_at,
                    'updated_at' => fake()->dateTimeBetween($order->created_at, 'now'),
                ]);
        }

        $this->info("✅ Created {$ordersForIllustrations->count()} illustrations");

        // Summary
        $this->newLine();
        $this->info('🎉 Fake data generation completed!');
        $this->table(
            ['Entity', 'Count'],
            [
                ['Users', $users->count()],
                ['Products', $products->count()],
                ['Orders', $totalOrders],
                ['Order Details', OrderDetail::count()],
                ['Order Payments', OrderPayment::count()],
                ['Illustrations', Illustration::count()],
            ]
        );

        return 0;
    }

    /**
     * Clean existing fake data (keeps admin users)
     */
    private function cleanFakeData(): void
    {
        $this->info('🧹 Cleaning existing fake data...');

        // Keep admin users
        $adminEmails = ['oddejade@gmail.com', 'jlevarato@pm.me'];

        OrderDetail::truncate();
        OrderPayment::truncate();
        Illustration::truncate();
        Order::truncate();
        Product::truncate();
        User::whereNotIn('email', $adminEmails)->delete();

        $this->info('✅ Fake data cleaned');
    }

    /**
     * Get random order count with realistic distribution
     */
    private function getRandomOrderCount(int $max): int
    {
        // Weight towards fewer orders: 40% have 0, 30% have 1, 20% have 2, 10% have 3+
        $rand = mt_rand(1, 100);

        if ($rand <= 40) {
            return 0;
        }
        if ($rand <= 70) {
            return 1;
        }
        if ($rand <= 90) {
            return 2;
        }

        return mt_rand(3, $max);
    }

    /**
     * Get weighted order status (more realistic distribution)
     */
    private function getWeightedOrderStatus(array $statuses): OrderStatus
    {
        $weights = [
            OrderStatus::NEW->value => 20,
            OrderStatus::IN_PROGRESS->value => 15,
            OrderStatus::PENDING_PAYMENT->value => 10,
            OrderStatus::PAID->value => 25,
            OrderStatus::TO_SHIP->value => 10,
            OrderStatus::SHIPPED->value => 15,
            OrderStatus::DONE->value => 5,
        ];

        $totalWeight = array_sum($weights);
        $rand = mt_rand(1, $totalWeight);
        $currentWeight = 0;

        foreach ($weights as $status => $weight) {
            $currentWeight += $weight;
            if ($rand <= $currentWeight) {
                return OrderStatus::from($status);
            }
        }

        return OrderStatus::NEW;
    }

    /**
     * Add realistic order details to an order
     */
    private function addOrderDetails(Order $order, $products): void
    {
        $productCount = fake()->numberBetween(1, 4);
        $selectedProducts = $products->random($productCount);

        foreach ($selectedProducts as $product) {
            OrderDetail::factory()
                ->create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => fake()->numberBetween(1, 3),
                    'price' => $product->price,
                ]);
        }
    }

    /**
     * Determine if order status should have payment
     */
    private function shouldHavePayment(OrderStatus $status): bool
    {
        return in_array($status, [
            OrderStatus::PAID,
            OrderStatus::TO_SHIP,
            OrderStatus::SHIPPED,
            OrderStatus::DONE,
        ]);
    }
}
