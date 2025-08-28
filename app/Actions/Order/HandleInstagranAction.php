<?php

namespace App\Actions\Order;

use App\Http\Requests\OrderRequest;
use App\Models\User;

class HandleInstagranAction
{
    public function handle(OrderRequest $request): void
    {
        $currentUser = User::select(['id', 'instagram'])->findOrFail($request->user()->id);
        $instagram = $request->get('user')['instagram'];

        if ($currentUser->instagram !== $instagram) {
            $currentUser->instagram = $request->get('user')['instagram'];
            $currentUser->save();
        }
    }
}
