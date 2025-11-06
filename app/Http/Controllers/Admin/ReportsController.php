<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Division;
use App\Models\Group;
use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use App\Models\InvCategory;
use Illuminate\Support\Facades\Auth;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\InventoryExport;

class ReportsController extends Controller
{
    public function index()
    {
        // Get quick stats for the dashboard
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        $todaysSales = Sale::whereDate('created_at', $today)->sum('total_amount');
        $todaysOrders = Sale::whereDate('created_at', $today)->count();
        $thisMonthSales = Sale::whereDate('created_at', '>=', $thisMonth)->sum('total_amount');

        // Calculate stock level percentage
        $totalProducts = Product::count();
        $lowStockProducts = Product::whereColumn('stock_quantity', '<=', 'min_stock_level')->count();
        $stockLevel = $totalProducts > 0 ? (($totalProducts - $lowStockProducts) / $totalProducts) * 100 : 100;

        return view('admin.reports.index', compact(
            'todaysSales',
            'todaysOrders',
            'thisMonthSales',
            'stockLevel'
        ));
    }

    public function salesByItem(Request $request)
    {
        $dateRange = $request->get('date_range', 'last_month');
        $categoryId = $request->get('category_id');
        $sortBy = $request->get('sort_by', 'revenue_desc');

        // Get date range
        $dates = $this->getDateRange($dateRange, $request);

        // Build query for sales by item
        $query = DB::table('sales')
            ->join('sale_items', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->leftJoin('groups', 'products.group_id', '=', 'groups.id')
            ->leftJoin('divisions', 'groups.division_id', '=', 'divisions.id')
            ->leftJoin('categories', 'divisions.category_id', '=', 'categories.id')
            ->whereBetween('sales.created_at', [$dates['start'], $dates['end']])
            ->select(
                'products.id',
                'products.name',
                'products.sku',
                'products.price',
                'products.cost_price',
                'categories.name as category_name',
                DB::raw('SUM(sale_items.quantity) as total_quantity'),
                DB::raw('SUM(sale_items.total_price) as total_revenue'),
                DB::raw('AVG(sale_items.unit_price) as avg_price'),
                DB::raw('SUM(sale_items.quantity * COALESCE(products.cost_price, 0)) as total_cost'),
                DB::raw('SUM(sale_items.total_price) - SUM(sale_items.quantity * COALESCE(products.cost_price, 0)) as profit')
            )
            ->groupBy('products.id', 'products.name', 'products.sku', 'products.price', 'products.cost_price', 'categories.name');

        // Apply category filter
        if ($categoryId) {
            $query->where('categories.id', $categoryId);
        }

        // Apply sorting
        switch ($sortBy) {
            case 'revenue_desc':
                $query->orderBy('total_revenue', 'desc');
                break;
            case 'revenue_asc':
                $query->orderBy('total_revenue', 'asc');
                break;
            case 'quantity_desc':
                $query->orderBy('total_quantity', 'desc');
                break;
            case 'quantity_asc':
                $query->orderBy('total_quantity', 'asc');
                break;
            case 'name_asc':
                $query->orderBy('products.name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('products.name', 'desc');
                break;
        }

        $salesData = $query->paginate(10);

        // Calculate summary stats
        $summaryQuery = DB::table('sales')
            ->join('sale_items', 'sales.id', '=', 'sale_items.sale_id')
            ->whereBetween('sales.created_at', [$dates['start'], $dates['end']]);

        if ($categoryId) {
            $summaryQuery->join('products', 'sale_items.product_id', '=', 'products.id')
                        ->leftJoin('groups', 'products.group_id', '=', 'groups.id')
                        ->leftJoin('divisions', 'groups.division_id', '=', 'divisions.id')
                        ->leftJoin('categories', 'divisions.category_id', '=', 'categories.id')
                        ->where('categories.id', $categoryId);
        }

        $summary = $summaryQuery->selectRaw('
            SUM(sale_items.total_price) as total_revenue,
            SUM(sale_items.quantity) as total_quantity,
            COUNT(DISTINCT sale_items.product_id) as unique_products
        ')->first();

        $totalRevenue = $summary->total_revenue ?? 0;
        $totalQuantity = $summary->total_quantity ?? 0;
        $uniqueProducts = $summary->unique_products ?? 0;
        $avgOrderValue = $totalQuantity > 0 ? $totalRevenue / $totalQuantity : 0;
        $totalOrders = Sale::whereBetween('created_at', [$dates['start'], $dates['end']])->count();

        // Get categories for filter
        $categories = Category::all();

        return view('admin.reports.sales-by-item', compact(
            'salesData',
            'totalRevenue',
            'totalQuantity',
            'uniqueProducts',
            'avgOrderValue',
            'totalOrders',
            'categories',
            'dateRange',
            'categoryId',
            'sortBy'
        ));
    }

    public function salesByCategory(Request $request)
    {
        $dateRange = $request->get('date_range', 'last_month');
        $brandId = $request->get('brand_id');
        $viewType = $request->get('view_type', 'category');

        // Get date range
        $dates = $this->getDateRange($dateRange, $request);

        // Build base query
        $baseQuery = DB::table('sales')
            ->join('sale_items', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('groups', 'products.group_id', '=', 'groups.id')
            ->join('divisions', 'groups.division_id', '=', 'divisions.id')
            ->join('categories', 'divisions.category_id', '=', 'categories.id')
            ->join('brands', 'categories.brand_id', '=', 'brands.id')
            ->whereBetween('sales.created_at', [$dates['start'], $dates['end']]);

        // Apply brand filter
        if ($brandId) {
            $baseQuery->where('brands.id', $brandId);
        }

        // Get category-wise data
        $categoryData = (clone $baseQuery)
            ->select(
                'categories.id',
                'categories.name',
                'categories.color',
                'brands.name as brand_name',
                DB::raw('SUM(sale_items.quantity) as total_quantity'),
                DB::raw('SUM(sale_items.total_price) as total_revenue'),
                DB::raw('COUNT(DISTINCT sales.id) as total_orders'),
                DB::raw('SUM(sale_items.quantity * COALESCE(products.cost_price, 0)) as total_cost'),
                DB::raw('SUM(sale_items.total_price) - SUM(sale_items.quantity * COALESCE(products.cost_price, 0)) as profit')
            )
            ->groupBy('categories.id', 'categories.name', 'categories.color', 'brands.name')
            ->orderBy('total_revenue', 'desc')
            ->get();

        // Calculate totals and percentages
        $totalRevenue = $categoryData->sum('total_revenue');
        $totalQuantity = $categoryData->sum('total_quantity');
        $totalOrders = Sale::whereBetween('created_at', [$dates['start'], $dates['end']])->count();
        $activeCategories = $categoryData->count();

        // Add percentage calculations
        $categoryData = $categoryData->map(function ($item) use ($totalRevenue) {
            $item->revenue_percentage = $totalRevenue > 0 ? ($item->total_revenue / $totalRevenue) * 100 : 0;
            $item->profit_margin = $item->total_revenue > 0 ? ($item->profit / $item->total_revenue) * 100 : 0;
            return $item;
        });

        // Get brands for filter
        $brands = Brand::all();

        return view('admin.reports.sales-by-category', compact(
            'categoryData',
            'totalRevenue',
            'totalQuantity',
            'totalOrders',
            'activeCategories',
            'brands',
            'dateRange',
            'brandId',
            'viewType'
        ));
    }

    public function inventoryStatus(Request $request)
    {
        $categoryId = $request->get('category_id');
        $stockStatus = $request->get('stock_status');
        $brandId = $request->get('brand_id');
        $locationId = $request->get('location_id');
        $sortBy = $request->get('sort_by', 'stock_asc');

        $companyId = Auth::user()->company_id ?? null;

        // Build query for Inventory Items (raw materials)
        $query = InventoryItem::with(['brand', 'category', 'division', 'group', 'stockUnit', 'location']);
        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        // Apply filters
        if ($categoryId) {
            $query->where('inv_category_id', $categoryId);
        }
        if ($brandId) {
            $query->where('brand_id', $brandId);
        }
        if ($locationId) {
            $query->where('location_id', $locationId);
        }

        if ($stockStatus) {
            switch ($stockStatus) {
                case 'in_stock':
                    $query->whereColumn('current_stock', '>', DB::raw('COALESCE(minimum_stock, 0)'));
                    break;
                case 'low_stock':
                    $query->whereColumn('current_stock', '<=', DB::raw('COALESCE(minimum_stock, 0)'))
                          ->where('current_stock', '>', 0);
                    break;
                case 'out_of_stock':
                    $query->where('current_stock', '<=', 0);
                    break;
            }
        }

        // Apply sorting
        switch ($sortBy) {
            case 'stock_asc':
                $query->orderBy('current_stock', 'asc');
                break;
            case 'stock_desc':
                $query->orderBy('current_stock', 'desc');
                break;
            case 'value_desc':
                $query->orderByRaw('(current_stock * cost_price) desc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
        }

        $inventoryData = $query->paginate(10);

        // Summary stats
        $base = InventoryItem::query();
        if ($companyId) { $base->where('company_id', $companyId); }

        $totalItems = (clone $base)->count();
        $lowStockItems = (clone $base)
            ->whereColumn('current_stock', '<=', DB::raw('COALESCE(minimum_stock, 0)'))
            ->where('current_stock', '>', 0)
            ->count();
        $outOfStockItems = (clone $base)->where('current_stock', '<=', 0)->count();
        $totalInventoryValue = (clone $base)
            ->selectRaw('SUM(current_stock * cost_price) as total_value')
            ->first()->total_value ?? 0;

        // Filter options
        $categories = InvCategory::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $locations = InventoryLocation::orderBy('name')->get();

        return view('admin.reports.inventory-status', compact(
            'inventoryData',
            'totalItems',
            'lowStockItems',
            'outOfStockItems',
            'totalInventoryValue',
            'categories',
            'brands',
            'locations',
            'categoryId',
            'stockStatus',
            'brandId',
            'locationId',
            'sortBy'
        ));
    }

    public function profitAnalysis(Request $request)
    {
        $dateRange = $request->get('date_range', 'last_month');
        $categoryId = $request->get('category_id');
        $marginFilter = $request->get('margin_filter');
        $sortBy = $request->get('sort_by', 'profit_desc');

        // Get date range
        $dates = $this->getDateRange($dateRange, $request);

        // Build query for profit analysis
        $query = DB::table('sales')
            ->join('sale_items', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->leftJoin('groups', 'products.group_id', '=', 'groups.id')
            ->leftJoin('divisions', 'groups.division_id', '=', 'divisions.id')
            ->leftJoin('categories', 'divisions.category_id', '=', 'categories.id')
            ->whereBetween('sales.created_at', [$dates['start'], $dates['end']])
            ->select(
                'products.id',
                'products.name',
                'products.sku',
                'products.price',
                'products.cost_price',
                'categories.name as category_name',
                DB::raw('SUM(sale_items.quantity) as units_sold'),
                DB::raw('SUM(sale_items.total_price) as total_revenue'),
                DB::raw('SUM(sale_items.quantity * COALESCE(products.cost_price, 0)) as total_cost'),
                DB::raw('SUM(sale_items.total_price) - SUM(sale_items.quantity * COALESCE(products.cost_price, 0)) as total_profit'),
                DB::raw('CASE
                    WHEN SUM(sale_items.total_price) > 0
                    THEN ((SUM(sale_items.total_price) - SUM(sale_items.quantity * COALESCE(products.cost_price, 0))) / SUM(sale_items.total_price)) * 100
                    ELSE 0
                END as profit_margin_percentage'),
                DB::raw('CASE
                    WHEN SUM(sale_items.quantity) > 0
                    THEN (SUM(sale_items.total_price) - SUM(sale_items.quantity * COALESCE(products.cost_price, 0))) / SUM(sale_items.quantity)
                    ELSE 0
                END as avg_profit_per_unit')
            )
            ->groupBy('products.id', 'products.name', 'products.sku', 'products.price', 'products.cost_price', 'categories.name')
            ->having('total_revenue', '>', 0);

        // Apply category filter
        if ($categoryId) {
            $query->where('categories.id', $categoryId);
        }

        // Apply margin filter
        if ($marginFilter) {
            switch ($marginFilter) {
                case 'high':
                    $query->havingRaw('((SUM(sale_items.total_price) - SUM(sale_items.quantity * COALESCE(products.cost_price, 0))) / SUM(sale_items.total_price)) * 100 > 30');
                    break;
                case 'medium':
                    $query->havingRaw('((SUM(sale_items.total_price) - SUM(sale_items.quantity * COALESCE(products.cost_price, 0))) / SUM(sale_items.total_price)) * 100 BETWEEN 10 AND 30');
                    break;
                case 'low':
                    $query->havingRaw('((SUM(sale_items.total_price) - SUM(sale_items.quantity * COALESCE(products.cost_price, 0))) / SUM(sale_items.total_price)) * 100 < 10');
                    break;
            }
        }

        // Apply sorting
        switch ($sortBy) {
            case 'profit_desc':
                $query->orderBy('total_profit', 'desc');
                break;
            case 'profit_asc':
                $query->orderBy('total_profit', 'asc');
                break;
            case 'margin_desc':
                $query->orderBy('profit_margin_percentage', 'desc');
                break;
            case 'revenue_desc':
                $query->orderBy('total_revenue', 'desc');
                break;
        }

        $profitData = $query->paginate(10);

        // Calculate summary stats
        $totalProfit = DB::table('sales')
            ->join('sale_items', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereBetween('sales.created_at', [$dates['start'], $dates['end']])
            ->selectRaw('SUM(sale_items.total_price) - SUM(sale_items.quantity * COALESCE(products.cost_price, 0)) as total_profit')
            ->first()->total_profit ?? 0;

        $totalRevenue = Sale::whereBetween('created_at', [$dates['start'], $dates['end']])->sum('total_amount');
        $totalCost = $totalRevenue - $totalProfit;
        $avgMargin = $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0;

        // Get category breakdown
        $categoryBreakdown = DB::table('sales')
            ->join('sale_items', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('groups', 'products.group_id', '=', 'groups.id')
            ->join('divisions', 'groups.division_id', '=', 'divisions.id')
            ->join('categories', 'divisions.category_id', '=', 'categories.id')
            ->whereBetween('sales.created_at', [$dates['start'], $dates['end']])
            ->select(
                'categories.name',
                'categories.color',
                DB::raw('SUM(sale_items.total_price) as revenue'),
                DB::raw('SUM(sale_items.quantity * COALESCE(products.cost_price, 0)) as cost'),
                DB::raw('SUM(sale_items.total_price) - SUM(sale_items.quantity * COALESCE(products.cost_price, 0)) as profit'),
                DB::raw('CASE
                    WHEN SUM(sale_items.total_price) > 0
                    THEN ((SUM(sale_items.total_price) - SUM(sale_items.quantity * COALESCE(products.cost_price, 0))) / SUM(sale_items.total_price)) * 100
                    ELSE 0
                END as margin_percentage')
            )
            ->groupBy('categories.id', 'categories.name', 'categories.color')
            ->orderBy('profit', 'desc')
            ->get();

        // Get categories for filter
        $categories = Category::all();

        return view('admin.reports.profit-analysis', compact(
            'profitData',
            'totalProfit',
            'totalRevenue',
            'totalCost',
            'avgMargin',
            'categoryBreakdown',
            'categories',
            'dateRange',
            'categoryId',
            'marginFilter',
            'sortBy'
        ));
    }

    private function getDateRange($dateRange, $request)
    {
        $now = Carbon::now();

        switch ($dateRange) {
            case 'today':
                return [
                    'start' => $now->copy()->startOfDay(),
                    'end' => $now->copy()->endOfDay()
                ];
            case 'yesterday':
                return [
                    'start' => $now->copy()->subDay()->startOfDay(),
                    'end' => $now->copy()->subDay()->endOfDay()
                ];
            case 'last_week':
                return [
                    'start' => $now->copy()->subWeek()->startOfWeek(),
                    'end' => $now->copy()->subWeek()->endOfWeek()
                ];
            case 'last_month':
                return [
                    'start' => $now->copy()->subMonth()->startOfMonth(),
                    'end' => $now->copy()->subMonth()->endOfMonth()
                ];
            case 'this_month':
                return [
                    'start' => $now->copy()->startOfMonth(),
                    'end' => $now->copy()->endOfMonth()
                ];
            case 'custom':
                return [
                    'start' => $request->get('start_date') ? Carbon::parse($request->get('start_date'))->startOfDay() : $now->copy()->subMonth()->startOfMonth(),
                    'end' => $request->get('end_date') ? Carbon::parse($request->get('end_date'))->endOfDay() : $now->copy()->endOfMonth()
                ];
            default:
                return [
                    'start' => $now->copy()->subMonth()->startOfMonth(),
                    'end' => $now->copy()->subMonth()->endOfMonth()
                ];
        }
    }

    // Export methods (placeholder for future implementation)
    public function exportSalesByItem($format)
    {
        return response()->json(['message' => 'Export functionality coming soon']);
    }

    public function exportSalesByCategory($format)
    {
        return response()->json(['message' => 'Export functionality coming soon']);
    }

    public function exportInventoryStatus($format)
    {
        return response()->json(['message' => 'Export functionality coming soon']);
    }

    public function exportProfitAnalysis($format)
    {
        return response()->json(['message' => 'Export functionality coming soon']);
    }
    public function inventory(Request $request)
{
    $categoryId = $request->get('category_id');
    $brandId = $request->get('brand_id');
    $stockStatus = $request->get('stock_status');
    $sortBy = $request->get('sort_by', 'name_asc');

    $query = Product::query()
        ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
        ->leftJoin('brands', 'brands.id', '=', 'products.brand_id')
        ->select('products.*', 'categories.name as category_name', 'brands.name as brand_name');

    if ($categoryId) $query->where('products.category_id', $categoryId);
    if ($brandId) $query->where('products.brand_id', $brandId);

    if ($stockStatus == 'in_stock') $query->where('current_stock', '>', DB::raw('min_level'));
    elseif ($stockStatus == 'low_stock') $query->whereColumn('current_stock', '<=', 'min_level')->where('current_stock', '>', 0);
    elseif ($stockStatus == 'out_of_stock') $query->where('current_stock', '=', 0);

    switch ($sortBy) {
        case 'stock_asc': $query->orderBy('current_stock', 'asc'); break;
        case 'stock_desc': $query->orderBy('current_stock', 'desc'); break;
        case 'value_desc': $query->orderByRaw('(current_stock * unit_value) DESC'); break;
        case 'name_asc': $query->orderBy('products.name', 'asc'); break;
    }

    $inventoryData = $query->paginate(15);

    return view('reports.inventory', [
        'categories' => Category::all(),
        'brands' => Brand::all(),
        'categoryId' => $categoryId,
        'brandId' => $brandId,
        'stockStatus' => $stockStatus,
        'sortBy' => $sortBy,
        'inventoryData' => $inventoryData,
        'totalItems' => Product::count(),
        'lowStockCount' => Product::whereColumn('current_stock', '<=', 'min_level')->where('current_stock', '>', 0)->count(),
        'outOfStockCount' => Product::where('current_stock', 0)->count(),
        'totalInventoryValue' => Product::sum(DB::raw('current_stock * unit_value')),
    ]);
}


public function exportInventoryExcel()
{
    $inventoryData = InventoryItem::select('code', 'name', 'current_stock', 'minimum_stock', 'cost_price')
        ->orderBy('name')
        ->get();

    $filename = 'inventory_items_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

    return Excel::download(new \App\Exports\InventoryExport($inventoryData), $filename);
}

public function exportInventoryPDF(Request $request)
{
    $companyId = Auth::user()->company_id ?? null;

    $items = InventoryItem::with(['brand','category','division','group','stockUnit','location'])
        ->when($companyId, fn($q) => $q->where('company_id', $companyId))
        ->orderBy('name')
        ->get();

    $pdf = Pdf::loadView('admin.reports.pdf.inventory-items-status', [
        'items' => $items,
        'generatedAt' => now(),
    ])->setPaper('a4', 'landscape');

    return $pdf->download('inventory_items_' . now()->format('Y-m-d_H-i-s') . '.pdf');
}


}