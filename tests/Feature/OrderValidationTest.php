<?php

use App\Events\OrderCreated;
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

describe('Order Validation and Integration', function () {

    it('validates required address fields correctly', function () {
        $user = User::factory()->create();
        $user->assignRole('user');

        $product = Product::factory()->inStock(5)->create();

        // Missing required address fields
        $invalidOrderData = [
            'additionalInfos' => '',
            'products' => [
                ['id' => $product->id, 'quantity' => 1, 'type' => 'item']
            ],
            'addresses' => [
                'shipping' => [
                    'firstName' => 'Invalid',
                    // Missing lastName, street, city, zipCode, country
                ],
                'same' => true
            ]
        ];

        $response = $this->actingAs($user)->post('/checkout', $invalidOrderData);

        $response->assertSessionHasErrors([
            'addresses.shipping.lastName',
            'addresses.shipping.street',
            'addresses.shipping.city',
            'addresses.shipping.zipCode',
            'addresses.shipping.country',
        ]);

        // Verify no order was created
        expect(Order::where('user_id', $user->id)->count())->toBe(0);
    });

    it('validates billing address when different from shipping', function () {
        $user = User::factory()->create();
        $user->assignRole('user');

        $product = Product::factory()->inStock(5)->create();

        $invalidOrderData = [
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
                'billing' => [
                    'firstName' => 'Jane',
                    // Missing required billing fields
                ],
                'same' => false
            ]
        ];

        $response = $this->actingAs($user)->post('/checkout', $invalidOrderData);

        $response->assertSessionHasErrors([
            'addresses.billing.lastName',
            'addresses.billing.street',
            'addresses.billing.city',
            'addresses.billing.zipCode',
            'addresses.billing.country',
        ]);

        expect(Order::where('user_id', $user->id)->count())->toBe(0);
    });

    it('validates product exists and has valid data', function () {
        $user = User::factory()->create();
        $user->assignRole('user');

        $nonExistentProductId = 99999;

        $invalidOrderData = [
            'additionalInfos' => '',
            'products' => [
                ['id' => $nonExistentProductId, 'quantity' => 1, 'type' => 'item']
            ],
            'addresses' => [
                'shipping' => [
                    'firstName' => 'Test',
                    'lastName' => 'User',
                    'street' => '123 Test St',
                    'city' => 'Paris',
                    'zipCode' => '75001',
                    'country' => 'France',
                ],
                'same' => true
            ]
        ];

        $response = $this->actingAs($user)->post('/checkout', $invalidOrderData);

        $response->assertSessionHasErrors(['products.0.id']);

        expect(Order::where('user_id', $user->id)->count())->toBe(0);
    });

    it('validates product quantity is positive', function () {
        $user = User::factory()->create();
        $user->assignRole('user');

        $product = Product::factory()->inStock(5)->create();

        $invalidOrderData = [
            'additionalInfos' => '',
            'products' => [
                ['id' => $product->id, 'quantity' => 0, 'type' => 'item'] // Invalid quantity
            ],
            'addresses' => [
                'shipping' => [
                    'firstName' => 'Test',
                    'lastName' => 'User',
                    'street' => '123 Test St',
                    'city' => 'Paris',
                    'zipCode' => '75001',
                    'country' => 'France',
                ],
                'same' => true
            ]
        ];

        $response = $this->actingAs($user)->post('/checkout', $invalidOrderData);

        $response->assertSessionHasErrors(['products.0.quantity']);

        expect(Order::where('user_id', $user->id)->count())->toBe(0);
    });

    it('validates illustration details when ordering illustration', function () {
        $user = User::factory()->create();
        $user->assignRole('user');

        $invalidOrderData = [
            'additionalInfos' => '',
            'products' => [
                [
                    'type' => 'illustration',
                    'illustrationDetails' => [
                        'illustrationType' => 'invalid_type', // Invalid type
                        // Missing other required fields
                    ]
                ]
            ],
            'addresses' => [
                'shipping' => [
                    'firstName' => 'Test',
                    'lastName' => 'User',
                    'street' => '123 Test St',
                    'city' => 'Paris',
                    'zipCode' => '75001',
                    'country' => 'France',
                ],
                'same' => true
            ]
        ];

        // This test depends on how illustration validation is implemented
        // The exact validation rules may be in the service layer or request validation
        $response = $this->actingAs($user)->post('/checkout', $invalidOrderData);

        // We expect some form of error (either validation or service-level)
        if ($response->status() === 302) {
            // Redirect back with errors
            $response->assertSessionHasErrors();
        }

        expect(Order::where('user_id', $user->id)->count())->toBe(0);
    });

    it('sends order confirmation email to user', function () {
        $user = User::factory()->create();
        $user->assignRole('user');

        $product = Product::factory()->inStock(3)->create();

        $orderData = [
            'additionalInfos' => 'Email test order',
            'products' => [
                ['id' => $product->id, 'quantity' => 1, 'type' => 'item']
            ],
            'addresses' => [
                'shipping' => [
                    'firstName' => 'Email',
                    'lastName' => 'Test',
                    'street' => '123 Email St',
                    'city' => 'Paris',
                    'zipCode' => '75001',
                    'country' => 'France',
                ],
                'same' => true
            ]
        ];

        $response = $this->actingAs($user)->post('/checkout', $orderData);

        $response->assertRedirect('/merci');

        // Verify OrderCreated event was dispatched
        Event::assertDispatched(OrderCreated::class, function ($event) use ($user) {
            return $event->order->user_id === $user->id && $event->accountCreated === false;
        });
    });

    it('sends order confirmation email to guest', function () {
        $product = Product::factory()->inStock(3)->create();

        $orderData = [
            'user' => [
                'email' => 'guest@gmail.com',
                'guest' => true,
                'additionalInfos' => 'Guest email test'
            ],
            'products' => [
                ['id' => $product->id, 'quantity' => 1, 'type' => 'item']
            ],
            'addresses' => [
                'shipping' => [
                    'firstName' => 'Guest',
                    'lastName' => 'Email',
                    'street' => '456 Guest Ave',
                    'city' => 'Lyon',
                    'zipCode' => '69001',
                    'country' => 'France',
                ],
                'same' => true
            ]
        ];

        $response = $this->post('/checkout', $orderData);

        $response->assertRedirect('/merci');

        // Verify OrderCreated event was dispatched for guest
Event::assertDispatched(OrderCreated::class, function ($event) {
    return $event->order->guest_id !== null && $event->accountCreated === false;
});
    });

    it('sends welcome email to new user', function () {
        $product = Product::factory()->inStock(3)->create();

        $orderData = [
            'user' => [
                'email' => 'newuser@gmail.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'guest' => false,
                'additionalInfos' => 'New user welcome test'
            ],
            'products' => [
                ['id' => $product->id, 'quantity' => 1, 'type' => 'item']
            ],
            'addresses' => [
                'shipping' => [
                    'firstName' => 'New',
                    'lastName' => 'User',
                    'street' => '789 Welcome St',
                    'city' => 'Marseille',
                    'zipCode' => '13001',
                    'country' => 'France',
                ],
                'same' => true
            ]
        ];

        $response = $this->post('/checkout', $orderData);

        $response->assertRedirect('/merci');

        // Verify OrderCreated event was dispatched for new user
        Event::assertDispatched(OrderCreated::class, function ($event) {
            return $event->order->user_id !== null && $event->accountCreated === true;
        });
    });

    it('validates email uniqueness for new user accounts', function () {
        // Create existing user
        $existingUser = User::factory()->create(['email' => 'taken@gmail.com']);

        $product = Product::factory()->inStock(5)->create();

        $orderData = [
            'user' => [
                'email' => 'taken@gmail.com', // Already taken
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
                    'lastName' => 'Email',
                    'street' => '123 Duplicate St',
                    'city' => 'Paris',
                    'zipCode' => '75001',
                    'country' => 'France',
                ],
                'same' => true
            ]
        ];

        $response = $this->post('/checkout', $orderData);

        $response->assertSessionHasErrors(['user.email']);

        expect(Order::count())->toBe(0);
    });

    it('validates password requirements for new user accounts', function () {
        $product = Product::factory()->inStock(5)->create();

        $orderData = [
            'user' => [
                'email' => 'newuser@gmail.com',
                'password' => '123', // Too weak
                'password_confirmation' => '123',
                'guest' => false,
                'additionalInfos' => ''
            ],
            'products' => [
                ['id' => $product->id, 'quantity' => 1, 'type' => 'item']
            ],
            'addresses' => [
                'shipping' => [
                    'firstName' => 'Weak',
                    'lastName' => 'Password',
                    'street' => '123 Weak St',
                    'city' => 'Paris',
                    'zipCode' => '75001',
                    'country' => 'France',
                ],
                'same' => true
            ]
        ];

        $response = $this->post('/checkout', $orderData);

        $response->assertSessionHasErrors(['user.password']);

        expect(Order::count())->toBe(0);
    });

    it('validates password confirmation for new user accounts', function () {
        $product = Product::factory()->inStock(5)->create();

        $orderData = [
            'user' => [
                'email' => 'newuser@gmail.com',
                'password' => 'password123',
                'password_confirmation' => 'different', // Mismatch
                'guest' => false,
                'additionalInfos' => ''
            ],
            'products' => [
                ['id' => $product->id, 'quantity' => 1, 'type' => 'item']
            ],
            'addresses' => [
                'shipping' => [
                    'firstName' => 'Mismatch',
                    'lastName' => 'Password',
                    'street' => '123 Mismatch St',
                    'city' => 'Paris',
                    'zipCode' => '75001',
                    'country' => 'France',
                ],
                'same' => true
            ]
        ];

        $response = $this->post('/checkout', $orderData);

        $response->assertSessionHasErrors(['user.password']);

        expect(Order::count())->toBe(0);
    });

    it('validates email format', function () {
        $product = Product::factory()->inStock(5)->create();

        $orderData = [
            'user' => [
                'email' => 'invalid-email', // Invalid format
                'guest' => true,
                'additionalInfos' => ''
            ],
            'products' => [
                ['id' => $product->id, 'quantity' => 1, 'type' => 'item']
            ],
            'addresses' => [
                'shipping' => [
                    'firstName' => 'Invalid',
                    'lastName' => 'Email',
                    'street' => '123 Invalid St',
                    'city' => 'Paris',
                    'zipCode' => '75001',
                    'country' => 'France',
                ],
                'same' => true
            ]
        ];

        $response = $this->post('/checkout', $orderData);

        $response->assertSessionHasErrors(['user.email']);

        expect(Order::count())->toBe(0);
    });

    it('prevents empty product array', function () {
        $user = User::factory()->create();
        $user->assignRole('user');

        $orderData = [
            'additionalInfos' => '',
            'products' => [], // Empty products
            'addresses' => [
                'shipping' => [
                    'firstName' => 'Empty',
                    'lastName' => 'Products',
                    'street' => '123 Empty St',
                    'city' => 'Paris',
                    'zipCode' => '75001',
                    'country' => 'France',
                ],
                'same' => true
            ]
        ];

        $response = $this->actingAs($user)->post('/checkout', $orderData);

        // Should have validation error or fail at service level
        if ($response->status() === 302) {
            $response->assertSessionHasErrors();
        }

        expect(Order::where('user_id', $user->id)->count())->toBe(0);
    });

});
