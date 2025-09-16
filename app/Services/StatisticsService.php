<?php

namespace App\Services;

use App\Enums\IllustrationStatus;
use App\Enums\OrderStatus;
use App\Models\Illustration;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Number;

class StatisticsService
{
    public function getFinancialStatistics(): array
    {
        [$lastWeek, $lastMonth] = $this->getRecentOrdersCount();

        return [
            'total_revenue' => $this->getTotalRevenue(),
            'average_order_value' => $this->getAverageOrderValue(),
            'last_week' => $lastWeek,
            'last_month' => $lastMonth,
            'orders_by_status' => $this->getOrdersByStatus(),
            'total_commands' => $this->getTotalCommands(),
        ];
    }

    public function getBusinessPerformanceStatistics(): array
    {
        return [
            'illustrations_stats' => $this->getIllustrationsStats(),
            'popular_illustration_types' => $this->getPopularIllustrationTypes(),
            'average_illustration_price' => $this->getAverageIllustrationPrice(),
            'print_vs_digital_ratio' => $this->getPrintVsDigitalRatio(),
        ];
    }

    public function getStocksStatistics(): array
    {
        return [
            'top_sellers' => $this->getTopSellersStats(),
            'low_stock_alerts' => $this->getLowStockAlerts(),
            'ouf_of_stock' => $this->getOutOfStockItems(),
        ];
    }

    public function getCustomerAnalytics(): array
    {
        return [
            'user_stats' => $this->getUserStats(),
            'top_customers' => $this->getTopCustomers(),
        ];
    }

    private function getTotalRevenue(): string
    {
        $totalRevenue = Order::whereIn('status', [
            OrderStatus::PAID,
            OrderStatus::TO_SHIP,
            OrderStatus::SHIPPED,
            OrderStatus::DONE,
        ])
            ->sum('total');

        return Number::currency($totalRevenue / 100, 'EUR', locale: 'fr');
    }

    private function getTotalCommands(): int
    {
        return Order::count();
    }

    private function getAverageOrderValue(): string
    {
        $completedOrders = Order::whereIn('status', [
            OrderStatus::PAID,
            OrderStatus::TO_SHIP,
            OrderStatus::SHIPPED,
            OrderStatus::DONE,
        ]);

        $totalRevenue = $completedOrders->sum('total');
        $orderCount = $completedOrders->count();
        $averageValue = $orderCount > 0 ? $totalRevenue / $orderCount : 0;

        return Number::currency($averageValue / 100, 'EUR', locale: 'fr');
    }

    private function getOrdersByStatus(): Collection
    {
        return Order::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->status->value => $item->count];
            });
    }

    private function getRecentOrdersCount(): array
    {
        $last7Days = Order::where('created_at', '>=', Carbon::now()->subDays(7))->count();
        $last30Days = Order::where('created_at', '>=', Carbon::now()->subDays(30))->count();

        return [
            $last7Days,
            $last30Days,
        ];
    }

    private function getIllustrationsStats(): array
    {
        $totalCommissioned = Illustration::count();
        $totalCompleted = Illustration::where('status', IllustrationStatus::COMPLETED)->count();

        return [
            'total_commissioned' => $totalCommissioned,
            'total_completed' => $totalCompleted,
        ];
    }

    private function getPopularIllustrationTypes(): Collection
    {
        return Illustration::select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->orderByDesc('count')
            ->get()
            ->map(function ($item) {
                return [
                    'type' => $item->type,
                    'count' => $item->count,
                ];
            });
    }

    private function getAverageIllustrationPrice(): string
    {
        $average = Illustration::avg('price');

        return Number::currency($average / 100, 'EUR', locale: 'fr');
    }

    private function getPrintVsDigitalRatio(): array
    {
        $printCount = Illustration::where('print', true)->count();
        $digitalCount = Illustration::where('print', false)->count();

        return [
            'print_count' => $printCount,
            'digital_count' => $digitalCount,
        ];
    }

    private function getTopSellersStats(): Collection
    {
        return Product::leftJoin('order_details', 'products.id', '=', 'order_details.product_id')
            ->select(
                'products.id',
                'products.name',
                DB::raw('COALESCE(SUM(order_details.quantity), 0) as total_sold')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_sold')
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'total_sold' => (int) $product->total_sold,
                ];
            });
    }

    private function getLowStockAlerts(): Collection
    {
        return Product::where('stock', '<', 5)
            ->where('stock', '>', 0)
            ->select('id', 'slug', 'name', 'stock')
            ->get();
    }

    private function getOutOfStockItems(): Collection
    {
        return Product::where('stock', 0)
            ->select('id', 'name', 'slug')
            ->get();
    }

    private function getUserStats(): array
    {
        $totalUsers = User::count();
        $guestOrders = Order::whereNotNull('guest_id')->count();
        $repeatCustomers = Order::select('user_id')
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->havingRaw('count(*) > 1')
            ->count();

        return [
            'total_registered_users' => $totalUsers,
            'guest_orders' => $guestOrders,
            'repeat_customers' => $repeatCustomers,
        ];
    }

    private function getTopCustomers(): Collection
    {
        return Order::join('users', 'orders.user_id', '=', 'users.id')
            ->select('users.email', DB::raw('count(*) as order_count'), DB::raw('sum(orders.total) as total_spent'))
            ->whereNotNull('orders.user_id')
            ->whereIn('orders.status', [
                OrderStatus::PAID,
                OrderStatus::TO_SHIP,
                OrderStatus::SHIPPED,
                OrderStatus::DONE,
            ])
            ->groupBy('users.id', 'users.email')
            ->orderByDesc('total_spent')
            ->limit(10)
            ->get()
            ->map(function ($customer) {
                return [
                    'email' => $customer->email,
                    'order_count' => $customer->order_count,
                    'total_spent' => Number::currency($customer->total_spent / 100, 'EUR', locale: 'fr'),
                ];
            });
    }
}
