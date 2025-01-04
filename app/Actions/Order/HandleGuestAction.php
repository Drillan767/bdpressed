<?php

namespace App\Actions\Order;

use App\Http\Requests\OrderRequest;
use App\Models\Guest;

class HandleGuestAction
{
    public function handle(OrderRequest $request): int
    {
        $guest = Guest::create([
            'email' => $request->get('user')['email'],
            'instagram' => $request->get('user')['instagram'],
        ]);

        return $guest->id;
    }
}
