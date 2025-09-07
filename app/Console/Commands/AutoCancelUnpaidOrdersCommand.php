<?php

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Command\Command as CommandAlias;

class AutoCancelUnpaidOrdersCommand extends Command
{
    protected $signature = 'orders:auto-cancel
                           {--dry-run : Show what would be processed without making changes}';

    protected $description = 'Auto-cancel unpaid orders after 7 days';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        $this->info('🔍 Checking for unpaid orders older than 7 days...');

        $cutoffDate = Carbon::now()->subDays(7);

        $unpaidOrders = Order::whereIn('status', [OrderStatus::NEW, OrderStatus::PENDING_PAYMENT])
            ->where('created_at', '<', $cutoffDate)
            ->with(['guest', 'user', 'details.product'])
            ->get();

        if ($unpaidOrders->isEmpty()) {
            $this->info('✅ No unpaid orders to cancel.');

            return Command::SUCCESS;
        }

        $this->warn("Found {$unpaidOrders->count()} unpaid orders to cancel:");
        $this->newLine();

        $cancelledCount = 0;
        foreach ($unpaidOrders as $order) {
            $customerInfo = $order->guest
                ? "guest ({$order->guest->email})"
                : "user ({$order->user->email})";

            $this->line("• Order {$order->reference} from {$customerInfo} created {$order->created_at->diffForHumans()}");

            if (! $dryRun) {
                try {
                    // Use our existing state machine transition which handles refunds automatically
                    $order->transitionTo(OrderStatus::CANCELLED, [
                        'triggered_by' => 'system',
                        'reason' => 'Auto-cancelled after 7 days without payment',
                    ]);

                    $cancelledCount++;
                } catch (\Exception $e) {
                    $this->error("❌ Failed to cancel order $order->reference: {$e->getMessage()}");

                    Log::error('Failed to auto-cancel expired order', [
                        'order_id' => $order->id,
                        'order_reference' => $order->reference,
                        'error' => $e->getMessage(),
                    ]);
                }
            } else {
                $cancelledCount++;
            }
        }

        $this->newLine();
        $this->info("✅ Auto-cancelled {$cancelledCount} unpaid orders");

        // Now trigger guest data anonymization
        if (! $dryRun) {
            $this->info('🔄 Triggering guest data anonymization...');
            $this->call('guests:anonymize-data');
        } else {
            $this->info('🔄 Would trigger guest data anonymization (skipped in dry-run)');
        }

        return CommandAlias::SUCCESS;
    }
}
