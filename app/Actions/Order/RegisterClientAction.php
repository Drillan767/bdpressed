<?php

namespace App\Actions\Order;

use App\Http\Requests\OrderRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class RegisterClientAction
{
    public function handle(OrderRequest $request): int
    {
        $user = User::create([
            'email' => $request->get('user')['email'],
            'instagram' => $request->get('user')['instagram'],
            'password' => Hash::make($request->get('user')['password']),
        ]);

        $user->assignRole('user');

        event(new Registered($user));

        Auth::login($user);

        return $user->id;
    }
}
