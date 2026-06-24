<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockService
{
    // ================================================================================================
    // 📦 STOCK DEDUCTION (on order placement)
    // ================================================================================================

    /**
     * Deduct stock for all items in an order from variant-level stocks.
     */
    public function deductOrderStock(Order $order): void
    {
        $order->loadMissing('items.product');

        foreach ($order->items as $item) {
            $this->deductProductStock($item->product, $item->quantity);
        }

        Log::info('Stock deducted for order', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
        ]);
    }

    /**
     * Deduct a specific quantity from a product's variant-level stocks.
     * Tries to deduct from active variants with sufficient stock.
     */
    public function deductProductStock(?Product $product, int $quantity): void
    {
        if (!$product) {
            return;
        }

        DB::transaction(function () use ($product, $quantity) {
            $variantStocks = $product->stocks()
                ->where('status', 'active')
                ->where('qty', '>', 0)
                ->orderBy('qty', 'desc')
                ->get();

            $remaining = $quantity;

            foreach ($variantStocks as $variant) {
                if ($remaining <= 0) {
                    break;
                }

                $deduct = min($variant->qty, $remaining);
                $variant->decrement('qty', $deduct);
                $remaining -= $deduct;

                if ($variant->qty <= 0) {
                    $variant->update(['status' => 'out_of_stock']);
                }
            }

            // Log insufficient stock warning if still remaining
            if ($remaining > 0) {
                Log::warning('Insufficient stock to fully deduct', [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'shortfall' => $remaining,
                ]);
            }
        });
    }

    // ================================================================================================
    // ♻️ STOCK RESTORATION (on cancel / return)
    // ================================================================================================

    /**
     * Restore stock for all items in an order (cancel entire order / return completed).
     */
    public function restoreOrderStock(Order $order): void
    {
        $order->loadMissing('items.product');

        foreach ($order->items as $item) {
            $this->restoreProductStock($item->product, $item->quantity);
        }

        Log::info('Stock restored for order', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
        ]);
    }

    /**
     * Restore stock for a single order item (item-level cancellation).
     */
    public function restoreOrderItemStock(OrderItem $item): void
    {
        $this->restoreProductStock($item->product, $item->quantity);

        Log::info('Stock restored for order item', [
            'order_item_id' => $item->id,
            'product_id' => $item->product_id,
            'quantity' => $item->quantity,
        ]);
    }

    /**
     * Restore quantity back to a product's variant-level stock.
     * Adds to the first available active variant, or creates one if none exist.
     */
    public function restoreProductStock(?Product $product, int $quantity): void
    {
        if (!$product) {
            return;
        }

        DB::transaction(function () use ($product, $quantity) {
            // Find an active variant to add stock back to
            $variant = $product->stocks()
                ->whereIn('status', ['active', 'out_of_stock'])
                ->first();

            if ($variant) {
                $variant->increment('qty', $quantity);
                if ($variant->status === 'out_of_stock' && $variant->qty > 0) {
                    $variant->update(['status' => 'active']);
                }
            } else {
                // No variant exists — create a default one
                $product->stocks()->create([
                    'sku'    => $product->slug . '-default',
                    'variant' => 'Default',
                    'price'  => $product->price,
                    'qty'    => $quantity,
                    'status' => 'active',
                ]);
            }
        });
    }

    // ================================================================================================
    // 🔍 STOCK QUERIES
    // ================================================================================================

    /**
     * Get total available stock across all active variants.
     */
    public function getAvailableStock(Product $product): int
    {
        return (int) $product->stocks()
            ->where('status', 'active')
            ->sum('qty');
    }

    /**
     * Check if a product has sufficient stock.
     */
    public function hasSufficientStock(Product $product, int $quantity = 1): bool
    {
        return $this->getAvailableStock($product) >= $quantity;
    }

    /**
     * Get stock status label.
     */
    public function getStockStatusLabel(Product $product, ?int $threshold = null): string
    {
        $qty = $this->getAvailableStock($product);
        $threshold = $threshold ?? 5;

        if ($qty === 0) {
            return 'Out of Stock';
        }
        if ($qty <= $threshold) {
            return 'Low Stock';
        }
        return 'In Stock';
    }

    /**
     * Get stock status badge class for UI.
     */
    public function getStockStatusBadgeClass(Product $product, ?int $threshold = null): string
    {
        $qty = $this->getAvailableStock($product);
        $threshold = $threshold ?? 5;

        if ($qty === 0) {
            return 'bg-danger';
        }
        if ($qty <= $threshold) {
            return 'bg-warning text-dark';
        }
        return 'bg-success';
    }

    /**
     * Validate stock before placing order.
     * Returns true if all items have sufficient stock, or a string error message.
     */
    public function validateOrderStock(Order $order): bool|string
    {
        $order->loadMissing('items.product');

        foreach ($order->items as $item) {
            $available = $this->getAvailableStock($item->product);
            if ($available < $item->quantity) {
                return "Insufficient stock for '{$item->product_name}'. Available: {$available}, Requested: {$item->quantity}.";
            }
        }

        return true;
    }

    /**
     * Get all products with computed stock quantities.
     * Returns collection with product + total_stock attribute.
     */
    public function getAllProductsWithStock()
    {
        return Product::with(['category', 'stocks'])
            ->withSum('stocks as total_stock', 'qty')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get low stock products.
     */
    public function getLowStockProducts(int $threshold = 5)
    {
        return Product::withSum('stocks as total_stock', 'qty')
            ->having('total_stock', '>', 0)
            ->having('total_stock', '<=', $threshold)
            ->orderBy('total_stock')
            ->get();
    }

    /**
     * Get out of stock products.
     */
    public function getOutOfStockProducts()
    {
        return Product::whereDoesntHave('stocks', function ($q) {
            $q->where('qty', '>', 0)->where('status', 'active');
        })->orWhereHas('stocks', function ($q) {
            $q->select(DB::raw('SUM(qty) as total'))->having('total', '<=', 0);
        })->get();
    }

    /**
     * Bulk update stock for a product variant.
     */
    public function updateVariantStock(int $variantId, int $newQty): ProductStock
    {
        $variant = ProductStock::findOrFail($variantId);
        $variant->update(['qty' => max(0, $newQty)]);

        // Auto-update status
        if ($variant->qty <= 0) {
            $variant->update(['status' => 'out_of_stock']);
        } elseif ($variant->status === 'out_of_stock') {
            $variant->update(['status' => 'active']);
        }

        return $variant->fresh();
    }
}
