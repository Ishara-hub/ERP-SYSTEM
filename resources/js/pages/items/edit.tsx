import { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { ArrowLeft, Save, X } from 'lucide-react';

interface Account {
    id: number;
    account_name: string;
    account_code: string;
}

interface Supplier {
    id: number;
    name: string;
}

interface ParentItem {
    id: number;
    item_name: string;
    item_type: string;
}

interface Item {
    id: number;
    item_name: string;
    item_number: string | null;
    item_type: string;
    cost: number | string;
    sales_price: number | string;
    on_hand: number | string;
    total_value: number | string;
    is_active: boolean;
    is_inactive: boolean;
    manufacturer_part_number: string | null;
    unit_of_measure: string | null;
    enable_unit_of_measure: boolean;
    purchase_description: string | null;
    sales_description: string | null;
    reorder_point: number | string | null;
    max_quantity: number | string | null;
    notes: string | null;
    parent_id: number | null;
    cogs_account_id: number | null;
    income_account_id: number | null;
    asset_account_id: number | null;
    preferred_vendor_id: number | null;
    is_used_in_assemblies: boolean;
    is_performed_by_subcontractor: boolean;
    purchase_from_vendor: boolean;
    build_point_min: number | string | null;
    cost_method: string;
}

interface ItemEditProps {
    item: Item;
    cogsAccounts: Account[];
    incomeAccounts: Account[];
    assetAccounts: Account[];
    suppliers: Supplier[];
    parentItems: ParentItem[];
}

export default function ItemEdit({ 
    item, 
    cogsAccounts, 
    incomeAccounts, 
    assetAccounts, 
    suppliers, 
    parentItems 
}: ItemEditProps) {
    const { data, setData, put, processing, errors } = useForm({
        item_name: item.item_name || '',
        item_number: item.item_number || '',
        item_type: item.item_type || 'Service',
        parent_id: item.parent_id || '',
        manufacturer_part_number: item.manufacturer_part_number || '',
        unit_of_measure: item.unit_of_measure || '',
        enable_unit_of_measure: item.enable_unit_of_measure || false,
        purchase_description: item.purchase_description || '',
        cost: typeof item.cost === 'number' ? item.cost.toString() : item.cost || '0',
        cost_method: item.cost_method || 'manual',
        cogs_account_id: item.cogs_account_id || '',
        preferred_vendor_id: item.preferred_vendor_id || '',
        sales_description: item.sales_description || '',
        sales_price: typeof item.sales_price === 'number' ? item.sales_price.toString() : item.sales_price || '0',
        income_account_id: item.income_account_id || '',
        asset_account_id: item.asset_account_id || '',
        reorder_point: typeof item.reorder_point === 'number' ? item.reorder_point.toString() : item.reorder_point || '',
        max_quantity: typeof item.max_quantity === 'number' ? item.max_quantity.toString() : item.max_quantity || '',
        on_hand: typeof item.on_hand === 'number' ? item.on_hand.toString() : item.on_hand || '0',
        is_used_in_assemblies: item.is_used_in_assemblies || false,
        is_performed_by_subcontractor: item.is_performed_by_subcontractor || false,
        purchase_from_vendor: item.purchase_from_vendor || false,
        build_point_min: typeof item.build_point_min === 'number' ? item.build_point_min.toString() : item.build_point_min || '',
        is_active: item.is_active || false,
        is_inactive: item.is_inactive || false,
        notes: item.notes || '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/items/${item.id}`);
    };

    const isInventoryItem = data.item_type.includes('Inventory');

    return (
        <AuthenticatedLayout>
            <Head title={`Edit Item: ${item.item_name}`} />
            
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Link href="/items">
                            <Button variant="outline" size="sm">
                                <ArrowLeft className="h-4 w-4 mr-2" />
                                Back to Items
                            </Button>
                        </Link>
                        <div>
                            <h1 className="text-3xl font-bold tracking-tight">Edit Item</h1>
                            <p className="text-muted-foreground">
                                Update item information and settings
                            </p>
                        </div>
                    </div>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    {/* Basic Information */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Basic Information</CardTitle>
                            <CardDescription>
                                Enter the basic details for this item
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="item_name">Item Name *</Label>
                                    <Input
                                        id="item_name"
                                        value={data.item_name}
                                        onChange={(e) => setData('item_name', e.target.value)}
                                        className={errors.item_name ? 'border-red-500' : ''}
                                    />
                                    {errors.item_name && (
                                        <p className="text-sm text-red-500">{errors.item_name}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="item_number">Item Number</Label>
                                    <Input
                                        id="item_number"
                                        value={data.item_number}
                                        onChange={(e) => setData('item_number', e.target.value)}
                                        className={errors.item_number ? 'border-red-500' : ''}
                                    />
                                    {errors.item_number && (
                                        <p className="text-sm text-red-500">{errors.item_number}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="item_type">Item Type *</Label>
                                    <Select value={data.item_type} onValueChange={(value) => setData('item_type', value)}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select item type" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="Service">Service</SelectItem>
                                            <SelectItem value="Inventory Part">Inventory Part</SelectItem>
                                            <SelectItem value="Inventory Assembly">Inventory Assembly</SelectItem>
                                            <SelectItem value="Non-Inventory Part">Non-Inventory Part</SelectItem>
                                            <SelectItem value="Other Charge">Other Charge</SelectItem>
                                            <SelectItem value="Discount">Discount</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    {errors.item_type && (
                                        <p className="text-sm text-red-500">{errors.item_type}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="parent_id">Parent Item</Label>
                                    <Select value={data.parent_id.toString()} onValueChange={(value) => setData('parent_id', value === 'none' ? '' : value)}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select parent item" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="none">No Parent Item</SelectItem>
                                            {parentItems.map((parent) => (
                                                <SelectItem key={parent.id} value={parent.id.toString()}>
                                                    {parent.item_name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="manufacturer_part_number">Manufacturer Part Number</Label>
                                    <Input
                                        id="manufacturer_part_number"
                                        value={data.manufacturer_part_number}
                                        onChange={(e) => setData('manufacturer_part_number', e.target.value)}
                                    />
                                </div>

                                <div className="space-y-2">
                                    <div className="flex items-center space-x-2">
                                        <Switch
                                            id="enable_unit_of_measure"
                                            checked={data.enable_unit_of_measure}
                                            onCheckedChange={(checked) => setData('enable_unit_of_measure', checked)}
                                        />
                                        <Label htmlFor="enable_unit_of_measure">Enable Unit of Measure</Label>
                                    </div>
                                    {data.enable_unit_of_measure && (
                                        <Input
                                            id="unit_of_measure"
                                            value={data.unit_of_measure}
                                            onChange={(e) => setData('unit_of_measure', e.target.value)}
                                            placeholder="e.g., hours, pieces, kg"
                                        />
                                    )}
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Pricing Information */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Pricing Information</CardTitle>
                            <CardDescription>
                                Set the cost and sales price for this item
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="cost">Cost *</Label>
                                    <Input
                                        id="cost"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        value={data.cost}
                                        onChange={(e) => setData('cost', e.target.value)}
                                        className={errors.cost ? 'border-red-500' : ''}
                                    />
                                    {errors.cost && (
                                        <p className="text-sm text-red-500">{errors.cost}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="sales_price">Sales Price *</Label>
                                    <Input
                                        id="sales_price"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        value={data.sales_price}
                                        onChange={(e) => setData('sales_price', e.target.value)}
                                        className={errors.sales_price ? 'border-red-500' : ''}
                                    />
                                    {errors.sales_price && (
                                        <p className="text-sm text-red-500">{errors.sales_price}</p>
                                    )}
                                </div>

                                {isInventoryItem && (
                                    <>
                                        <div className="space-y-2">
                                            <Label htmlFor="on_hand">On Hand Quantity</Label>
                                            <Input
                                                id="on_hand"
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                value={data.on_hand}
                                                onChange={(e) => setData('on_hand', e.target.value)}
                                            />
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor="reorder_point">Reorder Point</Label>
                                            <Input
                                                id="reorder_point"
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                value={data.reorder_point}
                                                onChange={(e) => setData('reorder_point', e.target.value)}
                                            />
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor="max_quantity">Max Quantity</Label>
                                            <Input
                                                id="max_quantity"
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                value={data.max_quantity}
                                                onChange={(e) => setData('max_quantity', e.target.value)}
                                            />
                                        </div>
                                    </>
                                )}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Account Information */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Account Information</CardTitle>
                            <CardDescription>
                                Set the accounts for this item
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="cogs_account_id">COGS Account</Label>
                                    <Select value={data.cogs_account_id.toString()} onValueChange={(value) => setData('cogs_account_id', value === 'none' ? '' : value)}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select COGS account" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="none">No COGS Account</SelectItem>
                                            {cogsAccounts.map((account) => (
                                                <SelectItem key={account.id} value={account.id.toString()}>
                                                    {account.account_name} ({account.account_code})
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="income_account_id">Income Account</Label>
                                    <Select value={data.income_account_id.toString()} onValueChange={(value) => setData('income_account_id', value === 'none' ? '' : value)}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select income account" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="none">No Income Account</SelectItem>
                                            {incomeAccounts.map((account) => (
                                                <SelectItem key={account.id} value={account.id.toString()}>
                                                    {account.account_name} ({account.account_code})
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="asset_account_id">Asset Account</Label>
                                    <Select value={data.asset_account_id.toString()} onValueChange={(value) => setData('asset_account_id', value === 'none' ? '' : value)}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select asset account" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="none">No Asset Account</SelectItem>
                                            {assetAccounts.map((account) => (
                                                <SelectItem key={account.id} value={account.id.toString()}>
                                                    {account.account_name} ({account.account_code})
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="preferred_vendor_id">Preferred Vendor</Label>
                                    <Select value={data.preferred_vendor_id.toString()} onValueChange={(value) => setData('preferred_vendor_id', value === 'none' ? '' : value)}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select preferred vendor" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="none">No Preferred Vendor</SelectItem>
                                            {suppliers.map((supplier) => (
                                                <SelectItem key={supplier.id} value={supplier.id.toString()}>
                                                    {supplier.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Descriptions */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Descriptions</CardTitle>
                            <CardDescription>
                                Add descriptions for purchase and sales
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="space-y-2">
                                <Label htmlFor="purchase_description">Purchase Description</Label>
                                <Textarea
                                    id="purchase_description"
                                    value={data.purchase_description}
                                    onChange={(e) => setData('purchase_description', e.target.value)}
                                    rows={3}
                                />
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="sales_description">Sales Description</Label>
                                <Textarea
                                    id="sales_description"
                                    value={data.sales_description}
                                    onChange={(e) => setData('sales_description', e.target.value)}
                                    rows={3}
                                />
                            </div>
                        </CardContent>
                    </Card>

                    {/* Status and Settings */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Status and Settings</CardTitle>
                            <CardDescription>
                                Configure item status and behavior
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="space-y-2">
                                    <div className="flex items-center space-x-2">
                                        <Switch
                                            id="is_active"
                                            checked={data.is_active}
                                            onCheckedChange={(checked) => setData('is_active', checked)}
                                        />
                                        <Label htmlFor="is_active">Active</Label>
                                    </div>
                                </div>

                                <div className="space-y-2">
                                    <div className="flex items-center space-x-2">
                                        <Switch
                                            id="is_inactive"
                                            checked={data.is_inactive}
                                            onCheckedChange={(checked) => setData('is_inactive', checked)}
                                        />
                                        <Label htmlFor="is_inactive">Inactive</Label>
                                    </div>
                                </div>

                                {isInventoryItem && (
                                    <>
                                        <div className="space-y-2">
                                            <div className="flex items-center space-x-2">
                                                <Switch
                                                    id="is_used_in_assemblies"
                                                    checked={data.is_used_in_assemblies}
                                                    onCheckedChange={(checked) => setData('is_used_in_assemblies', checked)}
                                                />
                                                <Label htmlFor="is_used_in_assemblies">Used in Assemblies</Label>
                                            </div>
                                        </div>

                                        <div className="space-y-2">
                                            <div className="flex items-center space-x-2">
                                                <Switch
                                                    id="purchase_from_vendor"
                                                    checked={data.purchase_from_vendor}
                                                    onCheckedChange={(checked) => setData('purchase_from_vendor', checked)}
                                                />
                                                <Label htmlFor="purchase_from_vendor">Purchase from Vendor</Label>
                                            </div>
                                        </div>
                                    </>
                                )}

                                <div className="space-y-2">
                                    <div className="flex items-center space-x-2">
                                        <Switch
                                            id="is_performed_by_subcontractor"
                                            checked={data.is_performed_by_subcontractor}
                                            onCheckedChange={(checked) => setData('is_performed_by_subcontractor', checked)}
                                        />
                                        <Label htmlFor="is_performed_by_subcontractor">Performed by Subcontractor</Label>
                                    </div>
                                </div>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="notes">Notes</Label>
                                <Textarea
                                    id="notes"
                                    value={data.notes}
                                    onChange={(e) => setData('notes', e.target.value)}
                                    rows={3}
                                />
                            </div>
                        </CardContent>
                    </Card>

                    {/* Actions */}
                    <div className="flex items-center justify-end gap-2">
                        <Link href="/items">
                            <Button type="button" variant="outline">
                                <X className="h-4 w-4 mr-2" />
                                Cancel
                            </Button>
                        </Link>
                        <Button type="submit" disabled={processing}>
                            <Save className="h-4 w-4 mr-2" />
                            {processing ? 'Saving...' : 'Save Changes'}
                        </Button>
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
