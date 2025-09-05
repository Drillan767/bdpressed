<?php

use App\Enums\OrderStatus;
use App\Events\OrderCreated;
use App\Models\IllustrationPrice;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Event::fake();
    Mail::fake();

    // Create roles for testing
    Role::create(['name' => 'admin']);
    Role::create(['name' => 'user']);

    IllustrationPrice::unsetEventDispatcher();
    $this->artisan('db:seed', ['--class' => 'IllustrationPriceSeeder']);
});

describe('Order Process Scenarios', function () {

    it('handles single item order correctly', function () {
        $user = User::factory()->create();
        $user->assignRole('user');

        $product = Product::factory()->inStock(5)->withPrice(2500)->create(); // €25

        $orderData = [
            'additionalInfos' => '',
            'products' => [
                ['id' => $product->id, 'quantity' => 2, 'type' => 'item']
            ],
            'addresses' => [
                'shipping' => [
                    'firstName' => 'Single',
                    'lastName' => 'Item',
                    'street' => '123 Test St',
                    'city' => 'Paris',
                    'zipCode' => '75001',
                    'country' => 'France',
                ],
                'same' => true
            ]
        ];

        $response = $this
            ->actingAs($user)
            ->post('/checkout', $orderData);

        $response->assertRedirect('/merci');

        $order = Order::where('user_id', $user->id)->first();
        expect($order)->not->toBeNull()
            ->and($order->total->cents())->toBe(5000)
            ->and($order->details)->toHaveCount(1)
            ->and($order->details->first()->quantity)->toBe(2);
        // 2 * €25
    });

    it('handles multiple items with different quantities', function () {
        $user = User::factory()->create();
        $user->assignRole('user');

        $product1 = Product::factory()->inStock(10)->withPrice(1500)->create(); // €15
        $product2 = Product::factory()->inStock(20)->withPrice(3000)->create(); // €30

        $orderData = [
            'additionalInfos' => '',
            'products' => [
                ['id' => $product1->id, 'quantity' => 2, 'type' => 'item'],
                ['id' => $product2->id, 'quantity' => 3, 'type' => 'item']
            ],
            'addresses' => [
                'shipping' => [
                    'firstName' => 'Multi',
                    'lastName' => 'Item',
                    'street' => '456 Test Ave',
                    'city' => 'Lyon',
                    'zipCode' => '69001',
                    'country' => 'France',
                ],
                'same' => true
            ]
        ];

        $response = $this
            ->actingAs($user)
            ->post('/checkout', $orderData);

        $response->assertRedirect('/merci');

        $order = Order::where('user_id', $user->id)->first();
        expect($order)->not->toBeNull()
            ->and($order->total->cents())->toBe(12000)
            ->and($order->details)->toHaveCount(2);
        // (2*€15) + (3*€30) = €30 + €90 = €120

        // Check individual order details
        $detail1 = $order->details->where('product_id', $product1->id)->first();
        $detail2 = $order->details->where('product_id', $product2->id)->first();

        expect($detail1->quantity)->toBe(2)
            ->and($detail1->price->cents())->toBe(3000)
            ->and($detail2->quantity)->toBe(3)
            ->and($detail2->price->cents())->toBe(9000);
        // 2 * €15
        // 3 * €30
    });

    it('rejects order when product is out of stock', function () {
        $user = User::factory()->create();
        $user->assignRole('user');

        $product = Product::factory()->outOfStock()->create();

        $orderData = [
            'additionalInfos' => '',
            'products' => [
                ['id' => $product->id, 'quantity' => 1, 'type' => 'item']
            ],
            'addresses' => [
                'shipping' => [
                    'firstName' => 'Out',
                    'lastName' => 'Stock',
                    'street' => '789 Test Blvd',
                    'city' => 'Marseille',
                    'zipCode' => '13001',
                    'country' => 'France',
                ],
                'same' => true
            ]
        ];

        $this
            ->actingAs($user)
            ->post('/checkout', $orderData);

        // The exact response depends on how stock validation is implemented
        // It might be a validation error or handled at service level
        // Let's check if no order was created as the minimum requirement
        expect(Order::where('user_id', $user->id)->count())->toBe(0);
    });

    it('handles mixed order with in-stock and out-of-stock items', function () {
        $user = User::factory()->create();
        $user->assignRole('user');

        $inStockProduct = Product::factory()->inStock(5)->create();
        $outOfStockProduct = Product::factory()->outOfStock()->create();

        $orderData = [
            'additionalInfos' => '',
            'products' => [
                ['id' => $inStockProduct->id, 'quantity' => 1, 'type' => 'item'],
                ['id' => $outOfStockProduct->id, 'quantity' => 1, 'type' => 'item']
            ],
            'addresses' => [
                'shipping' => [
                    'firstName' => 'Mixed',
                    'lastName' => 'Stock',
                    'street' => '321 Mixed St',
                    'city' => 'Nice',
                    'zipCode' => '06000',
                    'country' => 'France',
                ],
                'same' => true
            ]
        ];

        $this
            ->actingAs($user)
            ->post('/checkout', $orderData);

        // Should fail due to out of stock item
        expect(Order::where('user_id', $user->id)->count())->toBe(0);
    });

    it('handles order with one illustration', function () {
        $user = User::factory()->create();
        $user->assignRole('user');

        $orderData = [
            'additionalInfos' => 'Custom illustration order',
            'products' => [
                [
                    'type' => 'illustration',
                    'illustrationDetails' => [
                        'illustrationType' => 'BUST',
                        'addedHuman' => 1,
                        'addedAnimal' => 0,
                        'pose' => 'SIMPLE',
                        'background' => 'SIMPLE',
                        'print' => true,
                        'addTracking' => false,
                        'description' => 'Portrait of my character'
                    ]
                ]
            ],
            'addresses' => [
                'shipping' => [
                    'firstName' => 'Illustration',
                    'lastName' => 'Customer',
                    'street' => '321 Art St',
                    'city' => 'Nice',
                    'zipCode' => '06000',
                    'country' => 'France',
                ],
                'same' => true
            ]
        ];

        $response = $this
            ->actingAs($user)
            ->post('/checkout', $orderData);

        $response->assertRedirect('/merci');

        $order = Order::where('user_id', $user->id)->first();
        expect($order)->not->toBeNull()
            ->and($order->illustrations)->toHaveCount(1);

        $illustration = $order->illustrations->first();
        expect($illustration->type)->toBe('BUST')
            ->and($illustration->nbHumans)->toBe(1)
            ->and($illustration->nbAnimals)->toBe(0)
            ->and($illustration->pose)->toBe('SIMPLE')
            ->and($illustration->background)->toBe('SIMPLE')
            ->and($illustration->print)->toBeTrue()
            ->and($illustration->addTracking)->toBeFalse()
            ->and($illustration->description)->toBe('Portrait of my character');
    });

    it('handles order with multiple illustrations', function () {
        $user = User::factory()->create();
        $user->assignRole('user');

        $orderData = [
            'additionalInfos' => 'Multiple illustrations',
            'products' => [
                [
                    'type' => 'illustration',
                    'illustrationDetails' => [
                        'illustrationType' => 'BUST',
                        'addedHuman' => 1,
                        'addedAnimal' => 0,
                        'pose' => 'SIMPLE',
                        'background' => 'SIMPLE',
                        'print' => true,
                        'addTracking' => false,
                        'description' => 'First character'
                    ]
                ],
                [
                    'type' => 'illustration',
                    'illustrationDetails' => [
                        'illustrationType' => 'FULL_LENGTH',
                        'addedHuman' => 2,
                        'addedAnimal' => 1,
                        'pose' => 'COMPLEX',
                        'background' => 'COMPLEX',
                        'print' => false,
                        'addTracking' => true,
                        'description' => 'Group scene'
                    ]
                ]
            ],
            'addresses' => [
                'shipping' => [
                    'firstName' => 'Multi',
                    'lastName' => 'Illustration',
                    'street' => '654 Gallery Rd',
                    'city' => 'Cannes',
                    'zipCode' => '06400',
                    'country' => 'France',
                ],
                'same' => true
            ]
        ];

        $response = $this->actingAs($user)
            ->post('/checkout', $orderData);

        $response->assertRedirect('/merci');

        $order = Order::where('user_id', $user->id)->first();
        expect($order)->not->toBeNull()
            ->and($order->illustrations)->toHaveCount(2);

        $firstIllustration = $order->illustrations->where('type', 'BUST')->first();
        $secondIllustration = $order->illustrations->where('type', 'FULL_LENGTH')->first();

        expect($firstIllustration)->not->toBeNull()
            ->and($secondIllustration)->not->toBeNull()
            ->and($firstIllustration->description)->toBe('First character')
            ->and($secondIllustration->description)->toBe('Group scene')
            ->and($firstIllustration->nbHumans)->toBe(1)
            ->and($secondIllustration->nbHumans)->toBe(2)
            ->and($secondIllustration->nbAnimals)->toBe(1);
    });

    it('handles illustration-only order', function () {
        $user = User::factory()->create();
        $user->assignRole('user');

        $orderData = [
            'additionalInfos' => 'Only illustrations, no products',
            'products' => [
                [
                    'type' => 'illustration',
                    'illustrationDetails' => [
                        'illustrationType' => 'ANIMAL',
                        'addedHuman' => 0,
                        'addedAnimal' => 2,
                        'pose' => 'SIMPLE',
                        'background' => 'UNIFIED',
                        'print' => false,
                        'addTracking' => false,
                        'description' => 'Two cats together'
                    ]
                ]
            ],
            'addresses' => [
                'shipping' => [
                    'firstName' => 'Illustration',
                    'lastName' => 'Only',
                    'street' => '999 Pure Art Ave',
                    'city' => 'Avignon',
                    'zipCode' => '84000',
                    'country' => 'France',
                ],
                'same' => true
            ]
        ];

        $response = $this->actingAs($user)
            ->post('/checkout', $orderData);

        $response->assertRedirect('/merci');

        $order = Order::where('user_id', $user->id)->first();
        expect($order)->not->toBeNull()
            ->and($order->details)->toHaveCount(0)
            ->and($order->illustrations)->toHaveCount(1);
        // No physical products
        // One illustration

        $illustration = $order->illustrations->first();
        expect($illustration->type)->toBe('ANIMAL')
            ->and($illustration->nbAnimals)->toBe(2)
            ->and($illustration->description)->toBe('Two cats together')
            ->and($order->isIllustrationOnlyOrder())->toBeTrue();

        // Verify this is marked as illustration-only order
    });

    it('handles mixed order with items and illustrations', function () {
        $user = User::factory()->create();
        $user->assignRole('user');

        $product = Product::factory()->inStock(5)->withPrice(2000)->create(); // €20

        $orderData = [
            'additionalInfos' => 'Mixed order',
            'products' => [
                ['id' => $product->id, 'quantity' => 2, 'type' => 'item'],
                [
                    'type' => 'illustration',
                    'illustrationDetails' => [
                        'illustrationType' => 'ANIMAL',
                        'addedHuman' => 0,
                        'addedAnimal' => 1,
                        'pose' => 'SIMPLE',
                        'background' => 'UNIFIED',
                        'print' => true,
                        'addTracking' => false,
                        'description' => 'Pet portrait'
                    ]
                ]
            ],
            'addresses' => [
                'shipping' => [
                    'firstName' => 'Mixed',
                    'lastName' => 'Order',
                    'street' => '987 Combo St',
                    'city' => 'Strasbourg',
                    'zipCode' => '67000',
                    'country' => 'France',
                ],
                'same' => true
            ]
        ];

        $response = $this->actingAs($user)
            ->post('/checkout', $orderData);

        $response->assertRedirect('/merci');

        $order = Order::where('user_id', $user->id)->first();
        expect($order)->not->toBeNull()
            ->and($order->details)->toHaveCount(1)
            ->and($order->illustrations)->toHaveCount(1)
            ->and($order->details->first()->quantity)->toBe(2)
            ->and($order->details->first()->product_id)->toBe($product->id)
            ->and($order->details->first()->price->cents())->toBe(4000);
        // Physical products
        // Illustrations

        // Verify the physical product
        // 2 * €20

        // Verify the illustration
        $illustration = $order->illustrations->first();
        expect($illustration->type)->toBe('ANIMAL')
            ->and($illustration->description)->toBe('Pet portrait')
            ->and($illustration->print)->toBeTrue()
            ->and($order->isIllustrationOnlyOrder())->toBeFalse();

        // Verify this is not marked as illustration-only
    });

    it('calculates shipping fees correctly based on weight', function () {
        $user = User::factory()->create();
        $user->assignRole('user');

        // Light product (under 400g total)
        $lightProduct = Product::factory()->inStock(5)->withPrice(1000)->create(['weight' => 100]);

        $orderData = [
            'additionalInfos' => '',
            'products' => [
                ['id' => $lightProduct->id, 'quantity' => 2, 'type' => 'item'] // 200g total
            ],
            'addresses' => [
                'shipping' => [
                    'firstName' => 'Light',
                    'lastName' => 'Package',
                    'street' => '123 Light St',
                    'city' => 'Paris',
                    'zipCode' => '75001',
                    'country' => 'France',
                ],
                'same' => true
            ]
        ];

        $response = $this->actingAs($user)
            ->post('/checkout', $orderData);

        $order = Order::where('user_id', $user->id)->first();
        expect($order->shipmentFees->cents())->toBe(400); // €4 for light packages
    });

    it('calculates higher shipping fees for heavy packages', function () {
        $user = User::factory()->create();
        $user->assignRole('user');

        // Heavy product (over 400g total)
        $heavyProduct = Product::factory()->inStock(5)->withPrice(1000)->create(['weight' => 300]);

        $orderData = [
            'additionalInfos' => '',
            'products' => [
                ['id' => $heavyProduct->id, 'quantity' => 2, 'type' => 'item'] // 600g total
            ],
            'addresses' => [
                'shipping' => [
                    'firstName' => 'Heavy',
                    'lastName' => 'Package',
                    'street' => '456 Heavy Ave',
                    'city' => 'Lyon',
                    'zipCode' => '69001',
                    'country' => 'France',
                ],
                'same' => true
            ]
        ];

        $response = $this->actingAs($user)
            ->post('/checkout', $orderData);

        $order = Order::where('user_id', $user->id)->first();
        expect($order->shipmentFees->cents())->toBe(700); // €7 for heavy packages
    });

    it('includes illustration weight in shipping calculation', function () {
        $user = User::factory()->create();
        $user->assignRole('user');

        $orderData = [
            'additionalInfos' => '',
            'products' => [
                [
                    'type' => 'illustration',
                    'illustrationDetails' => [
                        'illustrationType' => 'BUST',
                        'addedHuman' => 1,
                        'addedAnimal' => 0,
                        'pose' => 'SIMPLE',
                        'background' => 'SIMPLE',
                        'print' => true, // Illustrations add 15g weight when printed
                        'addTracking' => false,
                        'description' => 'Test illustration'
                    ]
                ]
            ],
            'addresses' => [
                'shipping' => [
                    'firstName' => 'Illustration',
                    'lastName' => 'Weight',
                    'street' => '789 Art St',
                    'city' => 'Nice',
                    'zipCode' => '06000',
                    'country' => 'France',
                ],
                'same' => true
            ]
        ];

        $response = $this->actingAs($user)
            ->post('/checkout', $orderData);

        $order = Order::where('user_id', $user->id)->first();
        // Illustration adds 15g, which is under 400g threshold
        expect($order->shipmentFees->cents())->toBe(400); // €4 for light packages
    });

    it('ensures unique order references', function () {
        $user1 = User::factory()->create();
        $user1->assignRole('user');
        $user2 = User::factory()->create();
        $user2->assignRole('user');

        $product = Product::factory()->inStock(10)->create();

        $orderData1 = [
            'additionalInfos' => '',
            'products' => [
                ['id' => $product->id, 'quantity' => 1, 'type' => 'item']
            ],
            'addresses' => [
                'shipping' => [
                    'firstName' => 'User',
                    'lastName' => 'One',
                    'street' => '123 First St',
                    'city' => 'Paris',
                    'zipCode' => '75001',
                    'country' => 'France',
                ],
                'same' => true
            ]
        ];

        $orderData2 = [
            'additionalInfos' => '',
            'products' => [
                ['id' => $product->id, 'quantity' => 1, 'type' => 'item']
            ],
            'addresses' => [
                'shipping' => [
                    'firstName' => 'User',
                    'lastName' => 'Two',
                    'street' => '456 Second Ave',
                    'city' => 'Lyon',
                    'zipCode' => '69001',
                    'country' => 'France',
                ],
                'same' => true
            ]
        ];

        // Create both orders
        $this->actingAs($user1)->post('/checkout', $orderData1);
        $this->actingAs($user2)->post('/checkout', $orderData2);

        $order1 = Order::where('user_id', $user1->id)->first();
        $order2 = Order::where('user_id', $user2->id)->first();

        // Verify both orders have unique references
        expect($order1->reference)->not->toBe($order2->reference)
            ->and($order1->reference)->toMatch('/^[A-Z0-9]{8}$/')
            ->and($order2->reference)->toMatch('/^[A-Z0-9]{8}$/');
    });

});
