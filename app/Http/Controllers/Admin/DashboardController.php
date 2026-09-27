<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactSubmission;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /** Statuses considered as realized revenue. */
    private const REVENUE_STATUSES = ['paid', 'processing', 'shipped', 'delivered'];

    /** Number of days included in the rolling sales chart. */
    private const SALES_WINDOW_DAYS = 30;

    public function index(): View
    {
        $revenueTotal = Order::whereIn('status', self::REVENUE_STATUSES)
                             ->sum('total_cents');

        $recentOrders = Order::with('user')->latest()->limit(5)->get();

        $outOfStockCount = Product::outOfStock()->count();
        $lowStockCount   = Product::lowStock()->count();

        return view('admin.dashboard', [
            'userCount'       => User::count(),
            'productCount'    => Product::count(),
            'categoryCount'   => ProductCategory::count(),
            'adminCount'      => User::where('role', 'admin')->count(),
            'orderCount'      => Order::count(),
            'pendingCount'    => Order::whereIn('status', ['paid', 'processing'])->count(),
            'revenueTotal'    => $revenueTotal,
            'recentOrders'    => $recentOrders,
            'outOfStockCount' => $outOfStockCount,
            'lowStockCount'   => $lowStockCount,
            'analytics'       => $this->buildAnalytics(),
        ]);
    }

    /**
     * Build the JSON payload consumed by the Vue/Chart.js dashboard islands.
     *
     * - sales: rolling 30-day daily revenue (euros) + order count.
     * - inventory: snapshot of tracked product stock buckets + untracked count.
     * - topCategories: top 5 categories by realized revenue.
     * - customers: rolling 30-day daily new-customer registrations + repeat buyer ratio.
     * - contact: rolling 30-day daily contact submissions + total.
     */
    private function buildAnalytics(): array
    {
        return [
            'sales'         => $this->buildSalesSeries(),
            'inventory'     => $this->buildInventorySnapshot(),
            'topCategories' => $this->buildTopCategories(),
            'customers'     => $this->buildCustomerInsights(),
            'contact'       => $this->buildContactInsights(),
        ];
    }

    private function buildSalesSeries(): array
    {
        $start = Carbon::today()->subDays(self::SALES_WINDOW_DAYS - 1);

        // Aggregate by DATE(created_at). SQLite + MySQL both support DATE().
        $rows = Order::query()
            ->whereIn('status', self::REVENUE_STATUSES)
            ->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as day, SUM(total_cents) as revenue_cents, COUNT(*) as order_count')
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $labels   = [];
        $revenue  = [];
        $counts   = [];

        for ($i = 0; $i < self::SALES_WINDOW_DAYS; $i++) {
            $date = $start->copy()->addDays($i);
            $key  = $date->toDateString();
            $row  = $rows->get($key);

            $labels[]  = $date->format('d/m');
            $revenue[] = round(((int) ($row->revenue_cents ?? 0)) / 100, 2);
            $counts[]  = (int) ($row->order_count ?? 0);
        }

        return [
            'labels'  => $labels,
            'revenue' => $revenue,
            'orders'  => $counts,
        ];
    }

    private function buildInventorySnapshot(): array
    {
        $inStock    = Product::where('track_stock', true)
                             ->where('stock_quantity', '>', Product::LOW_STOCK_THRESHOLD)
                             ->count();
        $lowStock   = Product::lowStock()->count();
        $outOfStock = Product::outOfStock()->count();
        $untracked  = Product::where('track_stock', false)->count();

        return [
            'inStock'    => $inStock,
            'lowStock'   => $lowStock,
            'outOfStock' => $outOfStock,
            'untracked'  => $untracked,
        ];
    }

    private function buildTopCategories(): array
    {
        $rows = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('product_categories', 'product_categories.id', '=', 'products.product_category_id')
            ->whereIn('orders.status', self::REVENUE_STATUSES)
            ->selectRaw('COALESCE(product_categories.name, ?) as category, SUM(order_items.unit_price_cents * order_items.quantity) as revenue_cents', ['Sans catégorie'])
            ->groupBy('category')
            ->orderByDesc('revenue_cents')
            ->limit(5)
            ->get();

        return [
            'labels'  => $rows->pluck('category')->all(),
            'revenue' => $rows->map(fn ($r) => round(((int) $r->revenue_cents) / 100, 2))->all(),
        ];
    }

    private function buildCustomerInsights(): array
    {
        $start = Carbon::today()->subDays(self::SALES_WINDOW_DAYS - 1);

        $rows = User::query()
            ->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as new_users')
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $labels   = [];
        $newUsers = [];

        for ($i = 0; $i < self::SALES_WINDOW_DAYS; $i++) {
            $date = $start->copy()->addDays($i);
            $key  = $date->toDateString();
            $row  = $rows->get($key);

            $labels[]   = $date->format('d/m');
            $newUsers[] = (int) ($row->new_users ?? 0);
        }

        // Repeat-buyer ratio across realized orders.
        $buyerCounts = Order::query()
            ->whereIn('status', self::REVENUE_STATUSES)
            ->whereNotNull('user_id')
            ->select('user_id', DB::raw('COUNT(*) as order_count'))
            ->groupBy('user_id')
            ->get();

        $totalBuyers  = $buyerCounts->count();
        $repeatBuyers = $buyerCounts->where('order_count', '>', 1)->count();

        return [
            'labels'       => $labels,
            'newUsers'     => $newUsers,
            'totalBuyers'  => $totalBuyers,
            'repeatBuyers' => $repeatBuyers,
        ];
    }

    private function buildContactInsights(): array
    {
        $start = Carbon::today()->subDays(self::SALES_WINDOW_DAYS - 1);

        $rows = ContactSubmission::query()
            ->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as submissions')
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $labels      = [];
        $submissions = [];

        for ($i = 0; $i < self::SALES_WINDOW_DAYS; $i++) {
            $date = $start->copy()->addDays($i);
            $key  = $date->toDateString();
            $row  = $rows->get($key);

            $labels[]      = $date->format('d/m');
            $submissions[] = (int) ($row->submissions ?? 0);
        }

        $totalSubmissions = ContactSubmission::count();
        $recentSubjects   = ContactSubmission::query()
            ->where('created_at', '>=', $start)
            ->selectRaw('subject, COUNT(*) as count')
            ->groupBy('subject')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        return [
            'labels'           => $labels,
            'submissions'      => $submissions,
            'totalSubmissions' => $totalSubmissions,
            'topSubjects'      => $recentSubjects->map(fn ($r) => ['subject' => $r->subject, 'count' => (int) $r->count])->values()->all(),
        ];
    }
}
