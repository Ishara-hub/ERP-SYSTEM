<?php

namespace App\Services;

use App\Models\Item;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Record inventory purchase (for Enter Bill items).
     */
    public static function recordPurchase($item, $quantity, $unitPrice, $sourceDocument, $sourceDocumentId, $date = null, $description = null)
    {
        // Only process inventory items
        if (!in_array($item->item_type, [Item::INVENTORY_PART, Item::INVENTORY_ASSEMBLY])) {
            return null;
        }
        
        DB::transaction(function () use ($item, $quantity, $unitPrice, $sourceDocument, $sourceDocumentId, $date, $description) {
            // Update item on-hand quantity and total value
            $newOnHand = $item->on_hand + $quantity;
            $newTotalValue = $item->total_value + ($quantity * $unitPrice);
            
            $item->update([
                'on_hand' => $newOnHand,
                'total_value' => $newTotalValue,
                'as_of_date' => $date ?? now(),
            ]);
            
            // Create stock movement record
            StockMovement::create([
                'item_id' => $item->id,
                'quantity' => $quantity,
                'type' => 'purchase',
                'source_document' => $sourceDocument,
                'source_document_id' => $sourceDocumentId,
                'transaction_date' => $date ?? now(),
                'description' => $description ?? "Purchase from {$sourceDocument}",
                'reference_type' => 'purchase',
                'reference_id' => $sourceDocumentId,
            ]);
        });
        
        return true;
    }
    
    /**
     * Record inventory sale (for Invoice items).
     */
    public static function recordSale($item, $quantity, $sourceDocument, $sourceDocumentId, $date = null, $description = null, $allowNegative = true)
    {
        // Only process inventory items
        if (!in_array($item->item_type, [Item::INVENTORY_PART, Item::INVENTORY_ASSEMBLY])) {
            return null;
        }
        
        // Check if sufficient quantity available (only if not allowing negative)
        if (!$allowNegative && $item->on_hand < $quantity) {
            throw new \Exception("Insufficient inventory for {$item->item_name}. Available: {$item->on_hand}, Required: {$quantity}");
        }
        
        DB::transaction(function () use ($item, $quantity, $sourceDocument, $sourceDocumentId, $date, $description) {
            // Update item on-hand quantity and total value
            $newOnHand = $item->on_hand - $quantity;
            $newTotalValue = $item->total_value - ($quantity * $item->cost); // Use cost for COGS
            
            $item->update([
                'on_hand' => $newOnHand,
                'total_value' => max(0, $newTotalValue), // Don't go negative
                'as_of_date' => $date ?? now(),
            ]);
            
            // Create stock movement record (negative quantity for deduction)
            StockMovement::create([
                'item_id' => $item->id,
                'quantity' => -$quantity,
                'type' => 'sale',
                'source_document' => $sourceDocument,
                'source_document_id' => $sourceDocumentId,
                'transaction_date' => $date ?? now(),
                'description' => $description ?? "Sale from {$sourceDocument}",
                'reference_type' => 'sale',
                'reference_id' => $sourceDocumentId,
            ]);
        });
        
        return true;
    }
    
    /**
     * Record inventory adjustment.
     */
    public static function recordAdjustment($item, $quantity, $reason = null, $date = null, $description = null)
    {
        // Only process inventory items
        if (!in_array($item->item_type, [Item::INVENTORY_PART, Item::INVENTORY_ASSEMBLY])) {
            return null;
        }
        
        DB::transaction(function () use ($item, $quantity, $reason, $date, $description) {
            // Update item on-hand quantity and total value
            $newOnHand = $item->on_hand + $quantity;
            $valueChange = $quantity * $item->cost;
            $newTotalValue = $item->total_value + $valueChange;
            
            $item->update([
                'on_hand' => $newOnHand,
                'total_value' => max(0, $newTotalValue),
                'as_of_date' => $date ?? now(),
            ]);
            
            // Create stock movement record
            StockMovement::create([
                'item_id' => $item->id,
                'quantity' => $quantity,
                'type' => 'adjustment',
                'source_document' => 'adjustment',
                'source_document_id' => null,
                'transaction_date' => $date ?? now(),
                'description' => $description ?? ($quantity > 0 ? "Inventory adjustment - Increase" : "Inventory adjustment - Decrease"),
                'reference_type' => 'adjustment',
                'reference_id' => null,
            ]);
        });
        
        return true;
    }
}
