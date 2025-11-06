<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\InventoryAdjustment;
use Illuminate\Support\Facades\Auth;

class RecipeStockDeductionService
{
    /**
     * Deduct inventory based on recipes for all items in a sale.
     * - For each sale item, fetch product recipe items
     * - Convert usage quantity (usage unit) to stock units using stock_to_usage_factor
     * - Decrease InventoryItem.current_stock
     * - Record InventoryAdjustment with reason 'sale_deduction'
     */
    public function deductForSale(Sale $sale): void
    {
        // Ensure we have all relationships
        $sale->load(['saleItems.product.recipeItems.inventoryItem']);

        foreach ($sale->saleItems as $saleItem) {
            $product = $saleItem->product;
            if (!$product) { continue; }

            foreach ($product->recipeItems as $recipeItem) {
                $inv = $recipeItem->inventoryItem;
                if (!$inv) { continue; }

                $usageQty = (float) $recipeItem->usage_quantity; // in usage unit
                $qtySold = (int) $saleItem->quantity;
                $factor = (float) ($inv->stock_to_usage_factor ?? 0);
                // If stock unit and usage unit are the same, force factor to 1:1
                if (isset($inv->stock_unit_id, $inv->usage_unit_id) && (int)$inv->stock_unit_id === (int)$inv->usage_unit_id) {
                    $factor = 1.0;
                }
                if ($usageQty <= 0 || $qtySold <= 0) { continue; }

                // Total usage in usage units
                $totalUsage = $usageQty * $qtySold;

                // Convert to stock units; guard factor
                $deductStockUnits = $factor > 0 ? ($totalUsage / $factor) : $totalUsage;

                // Prevent ultra-small floating remainders from being rounded up unexpectedly
                if ($deductStockUnits > 0 && $deductStockUnits < 1e-6) {
                    $deductStockUnits = 0.0;
                }

                // Round to 6 decimals to match increased precision
                $deductStockUnits = round($deductStockUnits, 6);
                if ($deductStockUnits <= 0) { continue; }

                $before = (float) ($inv->current_stock ?? 0);
                $after = round($before - $deductStockUnits, 6);

                // Persist
                $inv->current_stock = $after;
                $inv->save();

                // Audit record
                InventoryAdjustment::create([
                    'company_id' => $inv->company_id,
                    'user_id' => $sale->user_id ?? (Auth::id() ?: null),
                    'inventory_item_id' => $inv->id,
                    'location_id' => $inv->location_id,
                    'adjustment_type' => 'subtract',
                    'quantity_before' => $before,
                    'quantity_after' => $after,
                    'adjustment_amount' => -$deductStockUnits,
                    'reason' => 'sale_deduction',
                    'notes' => sprintf(
                        'Sale %s - %s x%d | usage=%.6f x %d = %.6f; factor=%.6f usage/stock; deduct=%.6f stock',
                        $sale->sale_number ?? (string)$sale->id,
                        $product->name ?? 'Product',
                        $qtySold,
                        $usageQty,
                        $qtySold,
                        $totalUsage,
                        max($factor, 0),
                        $deductStockUnits
                    ),
                ]);
            }
        }
    }

    /**
     * Reverse recipe-based deduction (used when deleting/cancelling a sale).
     */
    public function restoreForSale(Sale $sale): void
    {
        $sale->load(['saleItems.product.recipeItems.inventoryItem']);

        foreach ($sale->saleItems as $saleItem) {
            $product = $saleItem->product;
            if (!$product) { continue; }

            foreach ($product->recipeItems as $recipeItem) {
                $inv = $recipeItem->inventoryItem;
                if (!$inv) { continue; }

                $usageQty = (float) $recipeItem->usage_quantity; // in usage unit
                $qtySold = (int) $saleItem->quantity;
                $factor = (float) ($inv->stock_to_usage_factor ?? 0);
                // If stock unit and usage unit are the same, force factor to 1:1
                if (isset($inv->stock_unit_id, $inv->usage_unit_id) && (int)$inv->stock_unit_id === (int)$inv->usage_unit_id) {
                    $factor = 1.0;
                }
                if ($usageQty <= 0 || $qtySold <= 0) { continue; }

                $totalUsage = $usageQty * $qtySold;
                $addStockUnits = $factor > 0 ? ($totalUsage / $factor) : $totalUsage;

                // Prevent ultra-small floating remainders from being rounded up unexpectedly
                if ($addStockUnits > 0 && $addStockUnits < 1e-6) {
                    $addStockUnits = 0.0;
                }

                $addStockUnits = round($addStockUnits, 6);
                if ($addStockUnits <= 0) { continue; }

                $before = (float) ($inv->current_stock ?? 0);
                $after = round($before + $addStockUnits, 6);

                $inv->current_stock = $after;
                $inv->save();

                InventoryAdjustment::create([
                    'company_id' => $inv->company_id,
                    'user_id' => $sale->user_id ?? (Auth::id() ?: null),
                    'inventory_item_id' => $inv->id,
                    'location_id' => $inv->location_id,
                    'adjustment_type' => 'add',
                    'quantity_before' => $before,
                    'quantity_after' => $after,
                    'adjustment_amount' => $addStockUnits,
                    'reason' => 'sale_delete_restore',
                    'notes' => sprintf(
                        'Restore from Sale %s - %s x%d | usage=%.6f x %d = %.6f; factor=%.6f usage/stock; add=%.6f stock',
                        $sale->sale_number ?? (string)$sale->id,
                        $product->name ?? 'Product',
                        $qtySold,
                        $usageQty,
                        $qtySold,
                        $totalUsage,
                        max($factor, 0),
                        $addStockUnits
                    ),
                ]);
            }
        }
    }
}

