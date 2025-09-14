<?php

namespace App\Services;

use App\Enums\IllustrationStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Comic;
use App\Models\ComicPage;
use App\Models\Illustration;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StatisticsService
{
    public function getFinancialStatistics(): array
    {
        return [
            'total_revenue' => $this->getTotalRevenue(),
            'average_order_value' => $this->getAverageOrderValue(),
            'orders_by_status' => $this->getOrdersByStatus(),
            'recent_orders' => $this->getRecentOrdersCount(),
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

    public function getProductsAndComicsStatistics(): array
    {
        return [
            'products_stats' => $this->getProductsStats(),
            'low_stock_alerts' => $this->getLowStockAlerts(),
            'comics_stats' => $this->getComicsStats(),
            'total_comic_pages' => $this->getTotalComicPages(),
        ];
    }

    public function getCustomerAnalytics(): array
    {
        return [
            'user_stats' => $this->getUserStats(),
            'repeat_customers' => $this->getRepeatCustomers(),
            'top_customers' => $this->getTopCustomers(),
        ];
    }

    public function getOperationalStatistics(): array
    {
        return [
            'pending_illustrations' => $this->getPendingIllustrations(),
            'orders_needing_tracking' => $this->getOrdersNeedingTracking(),
            'todays_orders' => $this->getTodaysOrders(),
        ];
    }

    public function getAllStatistics(): array
    {
        return [
            'financial' => $this->getFinancialStatistics(),
            'business_performance' => $this->getBusinessPerformanceStatistics(),
            'products_comics' => $this->getProductsAndComicsStatistics(),
            'customer_analytics' => $this->getCustomerAnalytics(),
            'operational' => $this->getOperationalStatistics(),
        ];
    }

    private function getTotalRevenue(): array
    {
        $completedOrders = Order::whereIn('status', [
            OrderStatus::PAID,
            OrderStatus::TO_SHIP,
            OrderStatus::SHIPPED,
            OrderStatus::DONE
        ]);

        $totalRevenue = $completedOrders->sum('total');
        $orderCount = $completedOrders->count();

        return [
            'amount' => $totalRevenue,
            'order_count' => $orderCount,
            'formatted_amount' => number_format($totalRevenue / 100, 2) . ' €'
        ];
    }

    private function getAverageOrderValue(): array
    {
        $completedOrders = Order::whereIn('status', [
            OrderStatus::PAID,
            OrderStatus::TO_SHIP,
            OrderStatus::SHIPPED,
            OrderStatus::DONE
        ]);

        $totalRevenue = $completedOrders->sum('total');
        $orderCount = $completedOrders->count();
        $averageValue = $orderCount > 0 ? $totalRevenue / $orderCount : 0;

        return [
            'amount' => $averageValue,
            'formatted_amount' => number_format($averageValue / 100, 2) . ' €'
        ];
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
            'last_7_days' => $last7Days,
            'last_30_days' => $last30Days,
        ];
    }

    private function getIllustrationsStats(): array
    {
        $totalCommissioned = Illustration::count();
        $totalCompleted = Illustration::where('status', IllustrationStatus::COMPLETED)->count();

        return [
            'total_commissioned' => $totalCommissioned,
            'total_completed' => $totalCompleted,
            'completion_rate' => $totalCommissioned > 0
                ? round(($totalCompleted / $totalCommissioned) * 100, 2)
                : 0
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
                    'count' => $item->count
                ];
            });
    }

    private function getAverageIllustrationPrice(): array
    {
        $average = Illustration::avg('price');

        return [
            'amount' => $average,
            'formatted_amount' => number_format($average / 100, 2) . ' €'
        ];
    }

    private function getPrintVsDigitalRatio(): array
    {
        $printCount = Illustration::where('print', true)->count();
        $digitalCount = Illustration::where('print', false)->count();
        $total = $printCount + $digitalCount;

        return [
            'print_count' => $printCount,
            'digital_count' => $digitalCount,
            'print_percentage' => $total > 0 ? round(($printCount / $total) * 100, 2) : 0,
            'digital_percentage' => $total > 0 ? round(($digitalCount / $total) * 100, 2) : 0,
        ];
    }

    private function getProductsStats(): array
    {
        $totalProducts = Product::count();
        $inStockProducts = Product::where('stock', '>', 0)->count();

        return [
            'total_products' => $totalProducts,
            'in_stock_products' => $inStockProducts,
            'out_of_stock_products' => $totalProducts - $inStockProducts,
        ];
    }

    private function getLowStockAlerts(): Collection
    {
        return Product::where('stock', '<', 5)
            ->where('stock', '>', 0)
            ->select('id', 'name', 'stock')
            ->get();
    }

    private function getComicsStats(): array
    {
        $totalComics = Comic::count();
        $publishedComics = Comic::where('is_published', true)->count();
        $unpublishedComics = $totalComics - $publishedComics;

        return [
            'total_comics' => $totalComics,
            'published_comics' => $publishedComics,
            'unpublished_comics' => $unpublishedComics,
        ];
    }

    private function getTotalComicPages(): int
    {
        return ComicPage::count();
    }

    private function getUserStats(): array
    {
        $totalUsers = User::count();
        $ordersWithUsers = Order::whereNotNull('user_id')->distinct('user_id')->count();
        $guestOrders = Order::whereNotNull('guest_id')->count();

        return [
            'total_registered_users' => $totalUsers,
            'users_with_orders' => $ordersWithUsers,
            'guest_orders' => $guestOrders,
        ];
    }

    private function getRepeatCustomers(): array
    {
        $repeatCustomers = Order::select('user_id')
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->havingRaw('count(*) > 1')
            ->count();

        $totalCustomersWithOrders = Order::whereNotNull('user_id')
            ->distinct('user_id')
            ->count();

        return [
            'count' => $repeatCustomers,
            'percentage' => $totalCustomersWithOrders > 0
                ? round(($repeatCustomers / $totalCustomersWithOrders) * 100, 2)
                : 0
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
                OrderStatus::DONE
            ])
            ->groupBy('users.id', 'users.email')
            ->orderByDesc('total_spent')
            ->limit(10)
            ->get()
            ->map(function ($customer) {
                return [
                    'email' => $customer->email,
                    'order_count' => $customer->order_count,
                    'total_spent' => $customer->total_spent,
                    'formatted_total_spent' => number_format($customer->total_spent / 100, 2) . ' €'
                ];
            });
    }

    private function getPendingIllustrations(): Collection
    {
        return Illustration::whereIn('status', [
            IllustrationStatus::DEPOSIT_PAID,
            IllustrationStatus::IN_PROGRESS,
            IllustrationStatus::CLIENT_REVIEW,
        ])
        ->with('order')
        ->select('id', 'order_id', 'type', 'status', 'created_at')
        ->get()
        ->map(function ($illustration) {
            return [
                'id' => $illustration->id,
                'order_reference' => $illustration->order->reference,
                'type' => $illustration->type,
                'status' => $illustration->status->value,
                'days_pending' => Carbon::parse($illustration->created_at)->diffInDays(Carbon::now())
            ];
        });
    }

    private function getOrdersNeedingTracking(): Collection
    {
        return Order::join('illustrations', 'orders.id', '=', 'illustrations.order_id')
            ->where('illustrations.addTracking', true)
            ->where(function ($query) {
                $query->whereNull('illustrations.trackingNumber')
                      ->orWhere('illustrations.trackingNumber', '');
            })
            ->where('orders.status', OrderStatus::TO_SHIP)
            ->select('orders.id', 'orders.reference', 'orders.created_at')
            ->distinct()
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'reference' => $order->reference,
                    'days_waiting' => Carbon::parse($order->created_at)->diffInDays(Carbon::now())
                ];
            });
    }

    private function getTodaysOrders(): array
    {
        $today = Carbon::today();
        $todaysOrders = Order::whereDate('created_at', $today);

        $totalCount = $todaysOrders->count();
        $needingProcessing = $todaysOrders->whereIn('status', [
            OrderStatus::NEW,
            OrderStatus::IN_PROGRESS,
            OrderStatus::PENDING_PAYMENT
        ])->count();

        return [
            'total_today' => $totalCount,
            'needing_processing' => $needingProcessing,
        ];
    }
}