<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Item extends Model
{
    protected $fillable = [
        'item_name', 'item_number', 'item_type', 'parent_id', 'manufacturer_part_number',
        'unit_of_measure', 'enable_unit_of_measure', 'purchase_description', 'cost',
        'cost_method', 'cogs_account_id', 'preferred_vendor_id', 'sales_description',
        'sales_price', 'markup_percentage', 'margin_percentage', 'income_account_id',
        'asset_account_id', 'reorder_point', 'max_quantity', 'on_hand', 'total_value',
        'as_of_date', 'is_used_in_assemblies', 'is_performed_by_subcontractor',
        'purchase_from_vendor', 'build_point_min', 'is_active', 'is_inactive',
        'notes', 'custom_fields'
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'sales_price' => 'decimal:2',
        'markup_percentage' => 'decimal:2',
        'margin_percentage' => 'decimal:2',
        'reorder_point' => 'decimal:2',
        'max_quantity' => 'decimal:2',
        'on_hand' => 'decimal:2',
        'total_value' => 'decimal:2',
        'build_point_min' => 'decimal:2',
        'enable_unit_of_measure' => 'boolean',
        'is_used_in_assemblies' => 'boolean',
        'is_performed_by_subcontractor' => 'boolean',
        'purchase_from_vendor' => 'boolean',
        'is_active' => 'boolean',
        'is_inactive' => 'boolean',
        'as_of_date' => 'date',
        'custom_fields' => 'array'
    ];

    // Constants for item types
    const SERVICE = 'Service';
    const INVENTORY_PART = 'Inventory Part';
    const INVENTORY_ASSEMBLY = 'Inventory Assembly';
    const NON_INVENTORY_PART = 'Non-Inventory Part';
    const OTHER_CHARGE = 'Other Charge';
    const DISCOUNT = 'Discount';
    const GROUP = 'Group';
    const PAYMENT = 'Payment';

    // Relationships
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Item::class, 'parent_id');
    }

    public function cogsAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'cogs_account_id');
    }

    public function incomeAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'income_account_id');
    }

    public function assetAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'asset_account_id');
    }

    public function preferredVendor(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'preferred_vendor_id');
    }

    // Assembly relationships
    public function assemblyComponents(): HasMany
    {
        return $this->hasMany(ItemComponent::class, 'assembly_item_id');
    }

    public function componentOf(): HasMany
    {
        return $this->hasMany(ItemComponent::class, 'component_item_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('is_inactive', false);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('item_type', $type);
    }

    public function scopeInventoryItems($query)
    {
        return $query->whereIn('item_type', [self::INVENTORY_PART, self::INVENTORY_ASSEMBLY]);
    }

    public function scopeServices($query)
    {
        return $query->where('item_type', self::SERVICE);
    }

    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    // Accessors
    public function getItemTypeColorAttribute(): string
    {
        return match($this->item_type) {
            self::SERVICE => 'text-blue-600',
            self::INVENTORY_PART => 'text-green-600',
            self::INVENTORY_ASSEMBLY => 'text-purple-600',
            self::NON_INVENTORY_PART => 'text-orange-600',
            self::OTHER_CHARGE => 'text-red-600',
            self::DISCOUNT => 'text-yellow-600',
            self::GROUP => 'text-gray-600',
            self::PAYMENT => 'text-indigo-600',
            default => 'text-gray-600'
        };
    }

    public function getItemTypeBgColorAttribute(): string
    {
        return match($this->item_type) {
            self::SERVICE => 'bg-blue-50',
            self::INVENTORY_PART => 'bg-green-50',
            self::INVENTORY_ASSEMBLY => 'bg-purple-50',
            self::NON_INVENTORY_PART => 'bg-orange-50',
            self::OTHER_CHARGE => 'bg-red-50',
            self::DISCOUNT => 'bg-yellow-50',
            self::GROUP => 'bg-gray-50',
            self::PAYMENT => 'bg-indigo-50',
            default => 'bg-gray-50'
        };
    }

    public function getFullPathAttribute(): string
    {
        $path = $this->item_name;
        $parent = $this->parent;
        
        while ($parent) {
            $path = $parent->item_name . ' > ' . $path;
            $parent = $parent->parent;
        }
        
        return $path;
    }

    public function getFormattedCostAttribute(): string
    {
        return '$' . number_format($this->cost, 2);
    }

    public function getFormattedSalesPriceAttribute(): string
    {
        return '$' . number_format($this->sales_price, 2);
    }

    public function getFormattedTotalValueAttribute(): string
    {
        return '$' . number_format($this->total_value, 2);
    }

    // Methods
    public function hasChildren(): bool
    {
        return $this->children()->count() > 0;
    }

    public function isAssembly(): bool
    {
        return $this->item_type === self::INVENTORY_ASSEMBLY;
    }

    public function isInventoryItem(): bool
    {
        return in_array($this->item_type, [self::INVENTORY_PART, self::INVENTORY_ASSEMBLY]);
    }

    public function isService(): bool
    {
        return $this->item_type === self::SERVICE;
    }

    public function calculateMarkup(): float
    {
        if ($this->cost == 0) return 0;
        return (($this->sales_price - $this->cost) / $this->cost) * 100;
    }

    public function calculateMargin(): float
    {
        if ($this->sales_price == 0) return 0;
        return (($this->sales_price - $this->cost) / $this->sales_price) * 100;
    }

    public function updateCalculatedFields(): void
    {
        $this->markup_percentage = $this->calculateMarkup();
        $this->margin_percentage = $this->calculateMargin();
        $this->total_value = $this->on_hand * $this->cost;
        $this->save();
    }
}