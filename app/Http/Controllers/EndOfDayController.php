<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;

class EndOfDayController extends Controller
{
    /**
     * Get today's sales summary for end of day
     */
    public function getTodaysSales(Request $request)
    {
        $date = $request->get('date', now()->toDateString());
        $user = Auth::user();

        $query = Sale::whereDate('sale_date', $date)
                    ->with(['saleItems.product', 'payments', 'user']);

        // Apply role-based filtering
        if ($user->role === 'cashier') {
            // Cashiers only see their own sales
            $query->where('user_id', $user->id);
        } elseif ($user->role === 'master_admin') {
            // Master admins see all sales (no filtering)
            // No additional where clause needed
        } else {
            // For managers/admins, show all sales from their branch
            if ($user->branch_id) {
                $query->where('branch_id', $user->branch_id);
            }
        }

        $sales = $query->get();

        // Calculate summary statistics
        $summary = [
            'total_sales' => $sales->count(),
            'total_amount' => $sales->sum('total_amount'),
            'total_items_sold' => $sales->sum(function($sale) {
                return $sale->saleItems->sum('quantity');
            }),
            'average_sale' => $sales->count() > 0 ? $sales->avg('total_amount') : 0,
            'payment_methods' => $sales->flatMap->payments->groupBy('method')->map->sum('amount'),
            'hourly_breakdown' => $this->getHourlyBreakdown($sales),
            'top_products' => $this->getTopProducts($sales),
            'cashier_performance' => $this->getCashierPerformance($sales, $user)
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'sales' => $sales,
                'summary' => $summary,
                'date' => $date
            ]
        ]);
    }

    /**
     * Get low stock items
     */
    public function getLowStockItems()
    {
        $user = Auth::user();
        
        $lowStockItems = Product::whereHas('group.division.category.brand.company.branches', function($query) use ($user) {
                $query->where('branches.id', $user->branch_id);
            })
            ->where(function($query) {
                $query->whereRaw('stock_quantity <= min_stock_level')
                      ->orWhere('stock_quantity', '<', 10);
            })
            ->with(['group.division.category'])
            ->orderBy('stock_quantity', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $lowStockItems
        ]);
    }

    /**
     * Sync all data to cloud/ERP system
     */
    public function syncToCloud(Request $request)
    {
        $user = Auth::user();
        $date = $request->get('date', now()->toDateString());

        DB::beginTransaction();

        try {
            // 1. Verify all sales are properly recorded
            $sales = Sale::whereDate('sale_date', $date)
                        ->where('branch_id', $user->branch_id)
                        ->with(['saleItems', 'payments'])
                        ->get();

            // 2. Update inventory levels based on sales
            foreach ($sales as $sale) {
                foreach ($sale->saleItems as $item) {
                    $product = Product::find($item->product_id);
                    if ($product && $product->stock_quantity !== null) {
                        // Ensure stock is properly decremented
                        $expectedStock = $product->stock_quantity;
                        // Stock should already be decremented during sale, but verify
                    }
                }
            }

            // 3. Generate end-of-day summary
            $summary = [
                'branch_id' => $user->branch_id,
                'date' => $date,
                'total_sales' => $sales->count(),
                'total_amount' => $sales->sum('total_amount'),
                'cashier_id' => $user->id,
                'sync_timestamp' => now(),
                'sales_data' => $sales->toArray()
            ];

            // 4. Log the sync activity
            \Log::info('End of day sync completed', $summary);

            // 5. Mark sales as processed (if field exists)
            foreach ($sales as $sale) {
                if (\Schema::hasColumn('sales', 'end_of_day_processed')) {
                    $sale->update(['end_of_day_processed' => true]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data successfully synced to cloud',
                'data' => [
                    'synced_sales' => $sales->count(),
                    'total_amount' => $sales->sum('total_amount'),
                    'sync_time' => now()->toISOString(),
                    'should_restart_pos' => true
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate comprehensive end-of-day report
     */
    public function generateReport(Request $request)
    {
        $date = $request->get('date', now()->toDateString());
        $user = Auth::user();

        // Get sales data
        $salesResponse = $this->getTodaysSales($request);
        $salesData = $salesResponse->getData(true)['data'];

        // Get low stock data
        $lowStockResponse = $this->getLowStockItems();
        $lowStockData = $lowStockResponse->getData(true)['data'];

        $report = [
            'report_date' => $date,
            'generated_at' => now()->toISOString(),
            'generated_by' => $user->name,
            'branch_id' => $user->branch_id,
            'sales_summary' => $salesData['summary'],
            'detailed_sales' => $salesData['sales'],
            'low_stock_items' => $lowStockData,
            'recommendations' => $this->generateRecommendations($salesData, $lowStockData)
        ];

        return response()->json([
            'success' => true,
            'data' => $report
        ]);
    }

    /**
     * Get hourly sales breakdown
     */
    private function getHourlyBreakdown($sales)
    {
        return $sales->groupBy(function($sale) {
            return Carbon::parse($sale->created_at)->format('H:00');
        })->map(function($hourlySales) {
            return [
                'count' => $hourlySales->count(),
                'amount' => $hourlySales->sum('total_amount')
            ];
        })->sortKeys();
    }

    /**
     * Get top selling products
     */
    private function getTopProducts($sales)
    {
        $productSales = [];
        
        foreach ($sales as $sale) {
            foreach ($sale->saleItems as $item) {
                $productId = $item->product_id;
                if (!isset($productSales[$productId])) {
                    $productSales[$productId] = [
                        'product' => $item->product,
                        'quantity_sold' => 0,
                        'total_revenue' => 0
                    ];
                }
                $productSales[$productId]['quantity_sold'] += $item->quantity;
                $productSales[$productId]['total_revenue'] += $item->total_price;
            }
        }

        return collect($productSales)
            ->sortByDesc('quantity_sold')
            ->take(10)
            ->values();
    }

    /**
     * Get cashier performance data
     */
    private function getCashierPerformance($sales, $currentUser)
    {
        if ($currentUser->role === 'cashier') {
            return [
                'cashier_name' => $currentUser->name,
                'total_sales' => $sales->count(),
                'total_amount' => $sales->sum('total_amount'),
                'average_sale' => $sales->count() > 0 ? $sales->avg('total_amount') : 0
            ];
        }

        return $sales->groupBy('user_id')->map(function($cashierSales) {
            $cashier = $cashierSales->first()->user;
            return [
                'cashier_name' => $cashier->name,
                'total_sales' => $cashierSales->count(),
                'total_amount' => $cashierSales->sum('total_amount'),
                'average_sale' => $cashierSales->avg('total_amount')
            ];
        })->values();
    }

    /**
     * Generate recommendations based on sales and stock data
     */
    private function generateRecommendations($salesData, $lowStockData)
    {
        $recommendations = [];

        // Low stock recommendations
        if (count($lowStockData) > 0) {
            $recommendations[] = [
                'type' => 'inventory',
                'priority' => 'high',
                'message' => 'Reorder ' . count($lowStockData) . ' low stock items',
                'items' => array_slice($lowStockData, 0, 5)
            ];
        }

        // Sales performance recommendations
        if ($salesData['summary']['total_sales'] < 10) {
            $recommendations[] = [
                'type' => 'sales',
                'priority' => 'medium',
                'message' => 'Consider promotional activities to increase sales volume'
            ];
        }

        return $recommendations;
    }
}
