<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Illustration;
use App\Services\IllustrationService;
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
        ;

        return Inertia::render('Admin/Illustrations/Index', compact('illustrations'));
    }

    public function show(
        Illustration $illustration,
        IllustrationService $illustrationService
    ): Response
    {
        $illustration->load(
            'statusChanges',
            'order:id,reference,user_id,guest_id',
            'order.user:id,email',
            'order.guest:id,email',
            'payments',
        );

        $details = $illustrationService->getSingleIllustrationDetail($illustration);
        $availableStatuses = $illustration->getAvailableStatuses();

        if ($illustration->order->guest_id) {
            $email = $illustration->order->guest->email;
        } else {
            $email = $illustration->order->user->email;
        }

        $client = [
            'email' => $email,
            'guest' => !!$illustration->order->guest_id,
        ];

        return Inertia::render('Admin/Illustrations/Show', compact(
            'illustration',
            'details',
            'availableStatuses',
            'client',
        ));
    }
}
