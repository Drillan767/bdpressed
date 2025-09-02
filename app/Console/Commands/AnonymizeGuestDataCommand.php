<?php

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Models\Guest;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AnonymizeGuestDataCommand extends Command
{
    protected $signature = 'guests:anonymize-data 
                           {--dry-run : Show what would be processed without making changes}';

    protected $description = 'Anonymize guest data for GDPR compliance after 2 weeks';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        $this->info('🔍 Checking for guest data requiring GDPR anonymization...');

        $cutoffDate = Carbon::now()->subWeeks(2);

        // Find guests whose orders are either:
        // 1. Completed/cancelled and older than 2 weeks
        // 2. All their orders are in terminal states (DONE/CANCELLED) and oldest terminal order is 2+ weeks old
        $guestsToAnonymize = Guest::whereHas('orders', function ($query) use ($cutoffDate) {
            $query->whereIn('status', [OrderStatus::DONE, OrderStatus::CANCELLED])
                ->where('updated_at', '<', $cutoffDate);
        })
        // Only guests who don't have any active orders
            ->whereDoesntHave('orders', function ($query) {
                $query->whereNotIn('status', [OrderStatus::DONE, OrderStatus::CANCELLED]);
            })
        // Exclude already anonymized guests
            ->where('email', '!=', 'anonyme@rgpd.fr')
            ->with(['orders' => function ($query) {
                $query->select('id', 'guest_id', 'reference', 'status', 'updated_at');
            }])
            ->get();

        if ($guestsToAnonymize->isEmpty()) {
            $this->info('✅ No guest data requiring anonymization.');

            return Command::SUCCESS;
        }

        $this->warn("Found {$guestsToAnonymize->count()} guest accounts to anonymize:");
        $this->newLine();

        $anonymizedCount = 0;
        foreach ($guestsToAnonymize as $guest) {
            $orderCount = $guest->orders->count();
            $oldestTerminalOrder = $guest->orders->sortBy('updated_at')->first();

            $this->line("• Guest {$guest->email} ({$orderCount} orders, oldest completed {$oldestTerminalOrder->updated_at->diffForHumans()})");

            if (! $dryRun) {
                try {
                    DB::transaction(function () use ($guest) {
                        // Anonymize guest data
                        $guest->update([
                            'email' => 'anonyme@rgpd.fr',
                            'firstName' => 'Anonymisé',
                            'lastName' => 'RGPD',
                            'instagram' => null,
                        ]);

                        // Anonymize addresses
                        $guest->shippingAddress?->update([
                            'firstName' => 'Anonymisé',
                            'lastName' => 'RGPD',
                            'street' => 'Adresse anonymisée',
                            'street2' => null,
                            'city' => 'Ville anonymisée',
                            'zipCode' => '00000',
                            'country' => 'Pays anonymisé',
                        ]);

                        $guest->billingAddress?->update([
                            'firstName' => 'Anonymisé',
                            'lastName' => 'RGPD',
                            'street' => 'Adresse anonymisée',
                            'street2' => null,
                            'city' => 'Ville anonymisée',
                            'zipCode' => '00000',
                            'country' => 'Pays anonymisé',
                        ]);
                    });

                    Log::info('Guest data anonymized for GDPR compliance', [
                        'guest_id' => $guest->id,
                        'original_email' => $guest->getOriginal('email'),
                        'order_count' => $guest->orders->count(),
                        'oldest_terminal_order_date' => $oldestTerminalOrder->updated_at->toDateString(),
                    ]);

                    $anonymizedCount++;
                } catch (\Exception $e) {
                    $this->error("❌ Failed to anonymize guest {$guest->email}: {$e->getMessage()}");

                    Log::error('Failed to anonymize guest data', [
                        'guest_id' => $guest->id,
                        'guest_email' => $guest->email,
                        'error' => $e->getMessage(),
                    ]);
                }
            } else {
                $anonymizedCount++;
            }
        }

        $this->newLine();
        $this->info("✅ Anonymized {$anonymizedCount} guest accounts for GDPR compliance");

        return Command::SUCCESS;
    }
}
