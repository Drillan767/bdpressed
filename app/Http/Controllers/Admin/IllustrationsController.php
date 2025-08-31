<?php

namespace App\Http\Controllers\Admin;

use App\Enums\IllustrationStatus;
use App\Http\Controllers\Controller;
use App\Models\Illustration;
use App\Services\IllustrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            });

        return Inertia::render('Admin/Illustrations/Index', compact('illustrations'));
    }

    public function show(
        Illustration $illustration,
        IllustrationService $illustrationService
    ): Response {
        $illustration->load(
            'statusChanges',
            'order:id,reference,user_id,guest_id',
            'order.user:id,email',
            'order.guest:id,email',
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
            'guest' => (bool) $illustration->order->guest_id,
        ];

        $illustration->loadMissing('payments');
        $paymentHistory = $illustration->payments->map->adminDisplay;

        return Inertia::render('Admin/Illustrations/Show', compact(
            'illustration',
            'details',
            'availableStatuses',
            'paymentHistory',
            'client',
        ));
    }

    public function updateStatus(
        Request $request,
        Illustration $illustration,
        IllustrationService $illustrationService,
    ): RedirectResponse {
        $request->validate([
            'status' => 'required|string',
        ]);

        try {
            $newStatus = IllustrationStatus::from($request->get('status'));

            // Use the state machine transition which handles payment creation automatically
            $illustration->transitionTo($newStatus, [
                'reason' => $request->get('reason'),
                'triggered_by' => 'manual',
                'user_id' => auth()->id(),
            ]);

            return back()->with('success', 'Illustration status updated successfully');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update illustration status: '.$e->getMessage());
        }
    }
}
