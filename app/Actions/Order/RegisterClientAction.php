<?php

namespace App\Actions\Order;

use App\Http\Requests\OrderRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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

        Auth::login($user);

        return $user->id;
    }
}
