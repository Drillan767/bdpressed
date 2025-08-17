<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Illustration;
use Inertia\Inertia;
use Inertia\Response;

class IllustrationsController extends Controller
{
    public function index(): Response
    {
        $illustrations = Illustration::with(
        'order:id,reference,user_id,guest_id',
            'order.user:id,email',
            'order.guest:id,email',
        )
            ->get([
                'id',
                'status',
                'price',
                'order_id',
                'created_at',
                'updated_at',
            ])
            ->map(function ($illustration) {
                if ($illustration->order->guest_id) {
                    $email = $illustration->order->guest->email;
                } else {
                    $email = $illustration->order->user->email;
                }

                return [
                    ...$illustration->toArray(),
                    'email' => $email,
                ];
            })
            /*
            ->map((fn ($illustration) => [
                ...[$illustration],
                'email' => $illustration->user?->email ?? $illustration->guest->email,
            ]))
            */
        ;

        return Inertia::render('Admin/Illustrations/Index', compact('illustrations'));
    }

    public function show(Illustration $illustration): Response
    {
        $illustration->load('statusChanges');
        return Inertia::render('Admin/Illustrations/Show', compact('illustration'));
    }
}
