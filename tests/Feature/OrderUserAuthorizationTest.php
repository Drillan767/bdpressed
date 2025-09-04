<?php

use App\Enums\OrderStatus;
use App\Events\OrderCreated;
use App\Models\Address;
use App\Models\Guest;
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
});

describe('Order Authorization', function () {

    it('prevents admin users from ordering', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $product = Product::factory()->inStock(5)->create();

        $orderData = [
            'additionalInfos' => '',
            'products' => [
                ['id' => $product->id, 'quantity' => 1, 'type' => 'item']
            ],
            'addresses' => [
                'shipping' => [
                    'firstName' => 'John',
                    'lastName' => 'Doe',
                    'street' => '123 Main St',
                    'city' => 'Paris',
                    'zipCode' => '75001',
                    'country' => 'France',
                ],
                'same' => true
            ]
        ];

        $response = $this->actingAs($admin)
            ->withoutMiddleware()
            ->post('/checkout', $orderData);

        $response->assertStatus(403);

        // Verify no order was created
        expect(Order::count())->toBe(0);
    });

    it('allows user without account to create order (new account)', function () {
        $product = Product::factory()->inStock(5)->create();

        $orderData = [
            'user' => [
                'email' => 'newuser@gmail.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'guest' => false,
                'additionalInfos' => 'Please handle with care'
            ],
            'products' => [
                ['id' => $product->id, 'quantity' => 2, 'type' => 'item']
            ],
            'addresses' => [
                'shipping' => [
                    'firstName' => 'Jane',
                    'lastName' => 'Smith',
                    'street' => '456 Oak Ave',
                    'city' => 'Lyon',
                    'zipCode' => '69001',
                    'country' => 'France',
                ],
                'same' => true
            ]
        ];

        $response = $this->withoutMiddleware()
            ->post('/checkout', $orderData);

        $response->assertRedirect('/merci');

        // Verify user was created
        $user = User::where('email', 'newuser@gmail.com')->first();
        expect($user)->not->toBeNull()
            ->and($user->hasRole('user'))->toBeTrue();

        // Verify order was created
        $order = Order::where('user_id', $user->id)->first();
        expect($order)->not->toBeNull()
            ->and($order->status)->toBe(OrderStatus::NEW)
            ->and($order->additionalInfos)->toBe('Please handle with care')
            ->and($order->details)->toHaveCount(1)
            ->and($order->details->first()->quantity)->toBe(2)
            ->and($order->details->first()->product_id)->toBe($product->id);

        // Verify order details

        // Verify addresses were created and are set as default
        $shippingAddress = Address::find($order->shipping_address_id);
        expect($shippingAddress)->not->toBeNull()
            ->and($shippingAddress->user_id)->toBe($user->id)
            ->and($shippingAddress->default_shipping)->toBeTrue()
            ->and($shippingAddress->default_billing)->toBeTrue();
        // Same address used for both
    });

    it('allows guest user to create order', function () {
        $product = Product::factory()->inStock(3)->create();

        $orderData = [
            'user' => [
                'email' => 'guest@gmail.com',
                'guest' => true,
                'additionalInfos' => ''
            ],
            'products' => [
                ['id' => $product->id, 'quantity' => 1, 'type' => 'item']
            ],
            'addresses' => [
                'shipping' => [
                    'firstName' => 'Guest',
                    'lastName' => 'User',
                    'street' => '789 Pine St',
                    'city' => 'Marseille',
                    'zipCode' => '13001',
                    'country' => 'France',
                ],
                'same' => true
            ]
        ];

        $response = $this->withoutMiddleware()
            ->post('/checkout', $orderData);

        $response->assertRedirect('/merci');

        // Verify guest was created
        $guest = Guest::where('email', 'guest@gmail.com')->first();
        expect($guest)->not->toBeNull();

        // Verify order was created
        $order = Order::where('guest_id', $guest->id)->first();
        expect($order)->not->toBeNull()
            ->and($order->status)->toBe(OrderStatus::NEW)
            ->and($order->user_id)->toBeNull();

        // Verify address was created for guest
        $address = Address::find($order->shipping_address_id);
        expect($address)->not->toBeNull()
            ->and($address->guest_id)->toBe($guest->id)
            ->and($address->user_id)->toBeNull();
    });

    it('allows authenticated user without saved address to create order', function () {
        $user = User::factory()->create();
        $user->assignRole('user');

        $product = Product::factory()->inStock(4)->create();

        $orderData = [
            'additionalInfos' => 'Fragile items',
            'products' => [
                ['id' => $product->id, 'quantity' => 1, 'type' => 'item']
            ],
            'addresses' => [
                'shipping' => [
                    'firstName' => 'Auth',
                    'lastName' => 'User',
                    'street' => '321 Elm St',
                    'city' => 'Toulouse',
                    'zipCode' => '31000',
                    'country' => 'France',
                ],
                'billing' => [
                    'firstName' => 'Auth',
                    'lastName' => 'User',
                    'street' => '654 Maple St',
                    'city' => 'Nice',
                    'zipCode' => '06000',
                    'country' => 'France',
                ],
                'same' => false
            ]
        ];

        $response = $this->actingAs($user)
            ->withoutMiddleware()
            ->post('/checkout', $orderData);

        $response->assertRedirect('/merci');

        // Verify order was created
        $order = Order::where('user_id', $user->id)->first();
        expect($order)->not->toBeNull()
            ->and($order->status)->toBe(OrderStatus::NEW)
            ->and($order->additionalInfos)->toBe('Fragile items')
            ->and($order->useSameAddress)->toBeFalse()
            ->and($order->shippingAddress->city)->toBe('Toulouse')
            ->and($order->billingAddress->city)->toBe('Nice');

        // Verify addresses were created and linked

        // Verify addresses are set as default
        $shippingAddress = Address::find($order->shipping_address_id);
        $billingAddress = Address::find($order->billing_address_id);

        expect($shippingAddress->user_id)->toBe($user->id)
            ->and($shippingAddress->default_shipping)->toBeTrue()
            ->and($billingAddress->user_id)->toBe($user->id)
            ->and($billingAddress->default_billing)->toBeTrue();
    });

    it('allows authenticated user with saved addresses to create order', function () {
        $user = User::factory()->create();
        $user->assignRole('user');

        $shippingAddress = Address::factory()->forUser($user)->create([
            'firstName' => 'John',
            'city' => 'Bordeaux',
        ]);
        $billingAddress = Address::factory()->forUser($user)->create([
            'firstName' => 'John',
            'city' => 'Lille',
        ]);

        $product = Product::factory()->inStock(2)->create();

        $orderData = [
            'additionalInfos' => 'Use saved addresses',
            'products' => [
                ['id' => $product->id, 'quantity' => 3, 'type' => 'item']
            ],
            'addresses' => [
                'shippingId' => $shippingAddress->id,
                'billingId' => $billingAddress->id,
            ]
        ];

        $response = $this->actingAs($user)
            ->withoutMiddleware()
            ->post('/checkout', $orderData);

        $response->assertRedirect('/merci');

        // Verify order was created with correct addresses
        $order = Order::where('user_id', $user->id)->first();
        expect($order)->not->toBeNull()
            ->and($order->shipping_address_id)->toBe($shippingAddress->id)
            ->and($order->billing_address_id)->toBe($billingAddress->id)
            ->and($order->useSameAddress)->toBeFalse();

        // The existing addresses should maintain their current default status
        // (not necessarily changed by using them in an order)
        $shippingAddress->refresh();
        $billingAddress->refresh();
        expect($shippingAddress->user_id)->toBe($user->id)
            ->and($billingAddress->user_id)->toBe($user->id);
    });

    it('prevents duplicate email registration', function () {
        // Create existing user
        $existingUser = User::factory()->create(['email' => 'existing@gmail.com']);

        $product = Product::factory()->inStock(5)->create();

        $orderData = [
            'user' => [
                'email' => 'existing@gmail.com', // Same email
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'guest' => false,
                'additionalInfos' => ''
            ],
            'products' => [
                ['id' => $product->id, 'quantity' => 1, 'type' => 'item']
            ],
            'addresses' => [
                'shipping' => [
                    'firstName' => 'Duplicate',
                    'lastName' => 'User',
                    'street' => '123 Duplicate St',
                    'city' => 'Paris',
                    'zipCode' => '75001',
                    'country' => 'France',
                ],
                'same' => true
            ]
        ];

        $response = $this->withoutMiddleware()
            ->post('/checkout', $orderData);

        $response->assertSessionHasErrors(['user.email']);

        // Verify no new order was created
        expect(Order::count())->toBe(0);
    });

    it('prevents existing guest from creating new guest account', function () {
        // Create existing guest
        $existingGuest = Guest::factory()->create(['email' => 'existing@gmail.com']);

        $product = Product::factory()->inStock(5)->create();

        $orderData = [
            'user' => [
                'email' => 'existing@gmail.com', // Same email
                'guest' => true,
                'additionalInfos' => ''
            ],
            'products' => [
                ['id' => $product->id, 'quantity' => 1, 'type' => 'item']
            ],
            'addresses' => [
                'shipping' => [
                    'firstName' => 'Duplicate',
                    'lastName' => 'Guest',
                    'street' => '123 Duplicate St',
                    'city' => 'Paris',
                    'zipCode' => '75001',
                    'country' => 'France',
                ],
                'same' => true
            ]
        ];

        $response = $this->withoutMiddleware()
            ->post('/checkout', $orderData);

        $response->assertSessionHasErrors(['user.email']);

        // Verify no new order was created
        expect(Order::count())->toBe(0);
    });

});
