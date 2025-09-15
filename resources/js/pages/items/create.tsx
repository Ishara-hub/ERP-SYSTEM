import { useState, useEffect } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Checkbox } from '@/components/ui/checkbox';
import { Badge } from '@/components/ui/badge';
import { 
    ArrowLeft, 
    Save, 
    X, 
    DollarSign, 
    Package, 
    Wrench, 
    Settings,
    Calculator,
    Info
} from 'lucide-react';

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

interface CreateItemProps {
    itemType: string;
    cogsAccounts: Account[];
    incomeAccounts: Account[];
    assetAccounts: Account[];
    suppliers: Supplier[];
    parentItems: ParentItem[];
}

const ITEM_TYPES = [
    { value: 'Service', label: 'Service', description: 'Use for services you charge for or purchase, like specialized labor, consulting hours, or professional fees.' },
    { value: 'Inventory Part', label: 'Inventory Part', description: 'Use for goods you purchase, track as inventory, and resell.' },
    { value: 'Inventory Assembly', label: 'Inventory Assembly', description: 'Use for inventory items that you assemble from other inventory items and then sell.' },
    { value: 'Non-Inventory Part', label: 'Non-Inventory Part', description: 'Use for goods you purchase and sell but don\'t track as inventory.' },
    { value: 'Other Charge', label: 'Other Charge', description: 'Use for miscellaneous charges like freight, handling, or other fees.' },
    { value: 'Discount', label: 'Discount', description: 'Use for discounts you give to customers.' },
    { value: 'Group', label: 'Group', description: 'Use to group related items together for reporting purposes.' },
    { value: 'Payment', label: 'Payment', description: 'Use for payment methods like cash, check, or credit card.' },
];

export default function CreateItem({ 
    itemType, 
    cogsAccounts, 
    incomeAccounts, 
    assetAccounts, 
    suppliers, 
    parentItems 
}: CreateItemProps) {
    const { data, setData, post, processing, errors } = useForm({
        item_name: '',
        item_number: '',
        item_type: itemType,
        parent_id: null as number | null,
        manufacturer_part_number: '',
        unit_of_measure: '',
        enable_unit_of_measure: false,
        purchase_description: '',
        cost: 0,
        cost_method: 'global_preference',
        cogs_account_id: null as number | null,
        preferred_vendor_id: null as number | null,
        sales_description: '',
        sales_price: 0,
        income_account_id: null as number | null,
        asset_account_id: null as number | null,
        reorder_point: null as number | null,
        max_quantity: null as number | null,
        on_hand: 0,
        is_used_in_assemblies: false,
        is_performed_by_subcontractor: false,
        purchase_from_vendor: false,
        build_point_min: null as number | null,
        is_active: true,
        is_inactive: false,
        notes: '',
        custom_fields: {} as Record<string, any>,
    });

    const [markup, setMarkup] = useState(0);
    const [margin, setMargin] = useState(0);
    const [totalValue, setTotalValue] = useState(0);

    // Calculate markup, margin, and total value when cost or sales price changes
    useEffect(() => {
        if (data.cost > 0) {
            setMarkup(((data.sales_price - data.cost) / data.cost) * 100);
        } else {
            setMarkup(0);
        }

        if (data.sales_price > 0) {
            setMargin(((data.sales_price - data.cost) / data.sales_price) * 100);
        } else {
            setMargin(0);
        }

        if (data.item_type.includes('Inventory')) {
            setTotalValue(data.on_hand * data.cost);
        } else {
            setTotalValue(0);
        }
    }, [data.cost, data.sales_price, data.on_hand, data.item_type]);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/items');
    };

    const getItemTypeIcon = (type: string) => {
        switch (type) {
            case 'Service':
                return <Wrench className="h-4 w-4" />;
            case 'Inventory Part':
            case 'Inventory Assembly':
                return <Package className="h-4 w-4" />;
            default:
                return <Settings className="h-4 w-4" />;
        }
    };

    const getItemTypeColor = (type: string) => {
        switch (type) {
            case 'Service':
                return 'bg-blue-50 text-blue-700 border-blue-200';
            case 'Inventory Part':
                return 'bg-green-50 text-green-700 border-green-200';
            case 'Inventory Assembly':
                return 'bg-purple-50 text-purple-700 border-purple-200';
            case 'Non-Inventory Part':
                return 'bg-orange-50 text-orange-700 border-orange-200';
            case 'Other Charge':
                return 'bg-red-50 text-red-700 border-red-200';
            case 'Discount':
                return 'bg-yellow-50 text-yellow-700 border-yellow-200';
            default:
                return 'bg-gray-50 text-gray-700 border-gray-200';
        }
    };

    const isInventoryItem = data.item_type.includes('Inventory');
    const isService = data.item_type === 'Service';
    const isAssembly = data.item_type === 'Inventory Assembly';

    return (
        <AuthenticatedLayout>
            <Head title="Create Item" />
            
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
                            <h1 className="text-3xl font-bold tracking-tight">Create Item</h1>
                            <p className="text-muted-foreground">
                                Add a new item to your inventory or service catalog
                            </p>
                        </div>
                    </div>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    <div className="grid gap-6 lg:grid-cols-3">
                        {/* Main Form */}
                        <div className="lg:col-span-2 space-y-6">
                            {/* Item Type Selection */}
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <Settings className="h-5 w-5" />
                                        TYPE
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="grid gap-2">
                                        <Label htmlFor="item_type">Item Type</Label>
                                        <Select 
                                            value={data.item_type} 
                                            onValueChange={(value) => setData('item_type', value)}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select item type" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {ITEM_TYPES.map((type) => (
                                                    <SelectItem key={type.value} value={type.value}>
                                                        <div className="flex items-center gap-2">
                                                            {getItemTypeIcon(type.value)}
                                                            {type.label}
                                                        </div>
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {errors.item_type && (
                                            <p className="text-sm text-red-600">{errors.item_type}</p>
                                        )}
                                    </div>
                                    
                                    <div className="p-3 bg-muted rounded-lg">
                                        <div className="flex items-center gap-2 mb-2">
                                            <Badge className={getItemTypeColor(data.item_type)}>
                                                {data.item_type}
                                            </Badge>
                                        </div>
                                        <p className="text-sm text-muted-foreground">
                                            {ITEM_TYPES.find(t => t.value === data.item_type)?.description}
                                        </p>
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Basic Information */}
                            <Card>
                                <CardHeader>
                                    <CardTitle>Basic Information</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="grid gap-4 md:grid-cols-2">
                                        <div className="space-y-2">
                                            <Label htmlFor="item_name">Item Name/Number *</Label>
                                            <Input
                                                id="item_name"
                                                value={data.item_name}
                                                onChange={(e) => setData('item_name', e.target.value)}
                                                placeholder="Enter item name"
                                                className={errors.item_name ? 'border-red-500' : ''}
                                            />
                                            {errors.item_name && (
                                                <p className="text-sm text-red-600">{errors.item_name}</p>
                                            )}
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor="item_number">Item Number</Label>
                                            <Input
                                                id="item_number"
                                                value={data.item_number}
                                                onChange={(e) => setData('item_number', e.target.value)}
                                                placeholder="Enter item number"
                                                className={errors.item_number ? 'border-red-500' : ''}
                                            />
                                            {errors.item_number && (
                                                <p className="text-sm text-red-600">{errors.item_number}</p>
                                            )}
                                        </div>
                                    </div>

                                    <div className="flex items-center space-x-2">
                                        <Checkbox
                                            id="is_subitem"
                                            checked={data.parent_id !== null}
                                            onCheckedChange={(checked) => {
                                                if (checked) {
                                                    
                                                } else {
                                                    setData('parent_id', null);
                                                }
                                            }}
                                        />
                                        <Label htmlFor="is_subitem">Subitem of</Label>
                                        {data.parent_id !== null && (
                                            <Select 
                                                value={data.parent_id?.toString() || 'none'} 
                                                onValueChange={(value) => setData('parent_id', value === 'none' ? null : parseInt(value))}
                                            >
                                                <SelectTrigger className="w-48">
                                                    <SelectValue placeholder="Select parent item" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="none">No Parent Item</SelectItem>
                                                    {parentItems.map((item) => (
                                                        <SelectItem key={item.id} value={item.id.toString()}>
                                                            {item.item_name}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                        )}
                                    </div>

                                    {data.item_type === 'Inventory Part' && (
                                        <div className="space-y-2">
                                            <Label htmlFor="manufacturer_part_number">Manufacturer's Part Number</Label>
                                            <Input
                                                id="manufacturer_part_number"
                                                value={data.manufacturer_part_number}
                                                onChange={(e) => setData('manufacturer_part_number', e.target.value)}
                                                placeholder="Enter manufacturer part number"
                                            />
                                        </div>
                                    )}
                                </CardContent>
                            </Card>

                            {/* Unit of Measure */}
                            <Card>
                                <CardHeader>
                                    <CardTitle>UNIT OF MEASURE</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="flex items-center space-x-2">
                                        <Switch
                                            id="enable_unit_of_measure"
                                            checked={data.enable_unit_of_measure}
                                            
                                        />
                                        <Label htmlFor="enable_unit_of_measure">Enable unit of measure</Label>
                                    </div>
                                    
                                    {data.enable_unit_of_measure && (
                                        <div className="space-y-2">
                                            <Label htmlFor="unit_of_measure">Unit of Measure</Label>
                                            <Input
                                                id="unit_of_measure"
                                                value={data.unit_of_measure}
                                                onChange={(e) => setData('unit_of_measure', e.target.value)}
                                                placeholder="e.g., Each, Box, Hour, etc."
                                            />
                                        </div>
                                    )}
                                </CardContent>
                            </Card>

                            {/* Service Specific Fields */}
                            {isService && (
                                <Card>
                                    <CardHeader>
                                        <CardTitle>Service Settings</CardTitle>
                                    </CardHeader>
                                    <CardContent className="space-y-4">
                                        <div className="flex items-center space-x-2">
                                        <Checkbox
                                            id="is_used_in_assemblies"
                                            checked={data.is_used_in_assemblies}
                                           
                                        />
                                            <Label htmlFor="is_used_in_assemblies">
                                                This service is used in assemblies or is performed by a subcontractor or partner
                                            </Label>
                                        </div>
                                    </CardContent>
                                </Card>
                            )}

                            {/* Assembly Specific Fields */}
                            {isAssembly && (
                                <Card>
                                    <CardHeader>
                                        <CardTitle>Assembly Settings</CardTitle>
                                    </CardHeader>
                                    <CardContent className="space-y-4">
                                        <div className="flex items-center space-x-2">
                                        <Checkbox
                                            id="purchase_from_vendor"
                                            checked={data.purchase_from_vendor}
                                            
                                        />
                                            <Label htmlFor="purchase_from_vendor">
                                                I purchase this assembly item from a vendor
                                            </Label>
                                        </div>
                                    </CardContent>
                                </Card>
                            )}

                            {/* Purchase Information */}
                            <Card>
                                <CardHeader>
                                    <CardTitle>PURCHASE INFORMATION</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="purchase_description">Description on Purchase Transactions</Label>
                                        <Textarea
                                            id="purchase_description"
                                            value={data.purchase_description}
                                            onChange={(e) => setData('purchase_description', e.target.value)}
                                            placeholder="Enter purchase description"
                                            rows={3}
                                        />
                                    </div>

                                    <div className="grid gap-4 md:grid-cols-2">
                                        <div className="space-y-2">
                                            <Label htmlFor="cost">Cost *</Label>
                                            <div className="relative">
                                                <DollarSign className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                                                <Input
                                                    id="cost"
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    value={data.cost}
                                                    onChange={(e) => setData('cost', parseFloat(e.target.value) || 0)}
                                                    className="pl-10"
                                                    placeholder="0.00"
                                                />
                                            </div>
                                            {errors.cost && (
                                                <p className="text-sm text-red-600">{errors.cost}</p>
                                            )}
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor="cost_method">Cost Method</Label>
                                            <Select 
                                                value={data.cost_method} 
                                                onValueChange={(value) => setData('cost_method', value)}
                                            >
                                                <SelectTrigger>
                                                    <SelectValue />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="global_preference">Use global preference</SelectItem>
                                                    <SelectItem value="manual">Manual entry</SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </div>
                                    </div>

                                    <div className="grid gap-4 md:grid-cols-2">
                                        <div className="space-y-2">
                                            <Label htmlFor="cogs_account_id">COGS Account</Label>
                                            <Select 
                                                value={data.cogs_account_id?.toString() || 'none'} 
                                                onValueChange={(value) => setData('cogs_account_id', value === 'none' ? null : parseInt(value))}
                                            >
                                                <SelectTrigger>
                                                    <SelectValue placeholder="Select COGS account" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="none">No COGS Account</SelectItem>
                                                    {cogsAccounts.map((account) => (
                                                        <SelectItem key={account.id} value={account.id.toString()}>
                                                            {account.account_code} · {account.account_name}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor="preferred_vendor_id">Preferred Vendor</Label>
                                            <Select 
                                                value={data.preferred_vendor_id?.toString() || 'none'} 
                                                onValueChange={(value) => setData('preferred_vendor_id', value === 'none' ? null : parseInt(value))}
                                            >
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

                            {/* Sales Information */}
                            <Card>
                                <CardHeader>
                                    <CardTitle>SALES INFORMATION</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="sales_description">Description on Sales Transactions</Label>
                                        <Textarea
                                            id="sales_description"
                                            value={data.sales_description}
                                            onChange={(e) => setData('sales_description', e.target.value)}
                                            placeholder="Enter sales description"
                                            rows={3}
                                        />
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="sales_price">Sales Price *</Label>
                                        <div className="relative">
                                            <DollarSign className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                                            <Input
                                                id="sales_price"
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                value={data.sales_price}
                                                onChange={(e) => setData('sales_price', parseFloat(e.target.value) || 0)}
                                                className="pl-10"
                                                placeholder="0.00"
                                            />
                                        </div>
                                        {errors.sales_price && (
                                            <p className="text-sm text-red-600">{errors.sales_price}</p>
                                        )}
                                    </div>

                                    {/* Markup and Margin Display */}
                                    <div className="grid gap-4 md:grid-cols-2">
                                        <div className="p-3 bg-muted rounded-lg">
                                            <div className="flex items-center gap-2 mb-1">
                                                <Calculator className="h-4 w-4 text-muted-foreground" />
                                                <span className="text-sm font-medium">Markup</span>
                                            </div>
                                            <div className="text-lg font-semibold">
                                                {markup.toFixed(1)}%
                                            </div>
                                        </div>
                                        <div className="p-3 bg-muted rounded-lg">
                                            <div className="flex items-center gap-2 mb-1">
                                                <Calculator className="h-4 w-4 text-muted-foreground" />
                                                <span className="text-sm font-medium">Margin</span>
                                            </div>
                                            <div className="text-lg font-semibold">
                                                {margin.toFixed(1)}%
                                            </div>
                                        </div>
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="income_account_id">Income Account</Label>
                                        <Select 
                                            value={data.income_account_id?.toString() || 'none'} 
                                            onValueChange={(value) => setData('income_account_id', value === 'none' ? null : parseInt(value))}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select income account" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="none">No Income Account</SelectItem>
                                                {incomeAccounts.map((account) => (
                                                    <SelectItem key={account.id} value={account.id.toString()}>
                                                        {account.account_code} · {account.account_name}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Inventory Information */}
                            {isInventoryItem && (
                                <Card>
                                    <CardHeader>
                                        <CardTitle>INVENTORY INFORMATION</CardTitle>
                                    </CardHeader>
                                    <CardContent className="space-y-4">
                                        <div className="space-y-2">
                                            <Label htmlFor="asset_account_id">Asset Account</Label>
                                            <Select 
                                                value={data.asset_account_id?.toString() || 'none'} 
                                                onValueChange={(value) => setData('asset_account_id', value === 'none' ? null : parseInt(value))}
                                            >
                                                <SelectTrigger>
                                                    <SelectValue placeholder="Select asset account" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="none">No Asset Account</SelectItem>
                                                    {assetAccounts.map((account) => (
                                                        <SelectItem key={account.id} value={account.id.toString()}>
                                                            {account.account_code} · {account.account_name}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                        </div>

                                        <div className="grid gap-4 md:grid-cols-2">
                                            <div className="space-y-2">
                                                <Label htmlFor="reorder_point">
                                                    {isAssembly ? 'Build Point (Min)' : 'Reorder Point (Min)'}
                                                </Label>
                                                <Input
                                                    id="reorder_point"
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    value={data.reorder_point || ''}
                                                    onChange={(e) => setData('reorder_point', parseFloat(e.target.value) || null)}
                                                    placeholder="0.00"
                                                />
                                            </div>

                                            <div className="space-y-2">
                                                <Label htmlFor="max_quantity">Max</Label>
                                                <Input
                                                    id="max_quantity"
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    value={data.max_quantity || ''}
                                                    onChange={(e) => setData('max_quantity', parseFloat(e.target.value) || null)}
                                                    placeholder="0.00"
                                                />
                                            </div>
                                        </div>

                                        <div className="grid gap-4 md:grid-cols-3">
                                            <div className="space-y-2">
                                                <Label htmlFor="on_hand">On Hand</Label>
                                                <Input
                                                    id="on_hand"
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    value={data.on_hand}
                                                    onChange={(e) => setData('on_hand', parseFloat(e.target.value) || 0)}
                                                    placeholder="0.00"
                                                />
                                            </div>

                                            <div className="space-y-2">
                                                <Label>Total Value</Label>
                                                <div className="p-3 bg-muted rounded-lg">
                                                    <div className="flex items-center gap-1">
                                                        <DollarSign className="h-4 w-4 text-muted-foreground" />
                                                        <span className="font-semibold">{totalValue.toFixed(2)}</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div className="space-y-2">
                                                <Label>As of</Label>
                                                <div className="p-3 bg-muted rounded-lg">
                                                    <span className="text-sm">{new Date().toLocaleDateString()}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </CardContent>
                                </Card>
                            )}

                            {/* Notes */}
                            <Card>
                                <CardHeader>
                                    <CardTitle>Additional Information</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="notes">Notes</Label>
                                        <Textarea
                                            id="notes"
                                            value={data.notes}
                                            onChange={(e) => setData('notes', e.target.value)}
                                            placeholder="Enter any additional notes about this item"
                                            rows={4}
                                        />
                                    </div>

                                    <div className="flex items-center space-x-2">
                                        <Switch
                                            id="is_inactive"
                                            checked={data.is_inactive}
                                            
                                        />
                                        <Label htmlFor="is_inactive">Item is inactive</Label>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                        {/* Action Buttons Sidebar */}
                        <div className="space-y-4">
                            <Card>
                                <CardHeader>
                                    <CardTitle>Actions</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-2">
                                    <Button type="submit" className="w-full" disabled={processing}>
                                        <Save className="h-4 w-4 mr-2" />
                                        {processing ? 'Creating...' : 'OK'}
                                    </Button>
                                    
                                    <Link href="/items">
                                        <Button variant="outline" className="w-full">
                                            <X className="h-4 w-4 mr-2" />
                                            Cancel
                                        </Button>
                                    </Link>

                                    <Button variant="outline" className="w-full" disabled>
                                        Next
                                    </Button>

                                    <Button variant="outline" className="w-full" disabled>
                                        Notes
                                    </Button>

                                    <Button variant="outline" className="w-full" disabled>
                                        Custom Fields
                                    </Button>

                                    <Button variant="outline" className="w-full" disabled>
                                        Spelling
                                    </Button>

                                    {data.sales_price > 0 && data.cost > 0 && (
                                        <Button variant="outline" className="w-full" disabled>
                                            Edit Markup...
                                        </Button>
                                    )}
                                </CardContent>
                            </Card>

                            {/* Help Links */}
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <Info className="h-4 w-4" />
                                        Help
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-2">
                                    <a 
                                        href="#" 
                                        className="text-blue-600 hover:text-blue-800 text-sm block"
                                    >
                                        How can I set rates by customers or employees?
                                    </a>
                                    
                                    {isAssembly && (
                                        <a 
                                            href="#" 
                                            className="text-blue-600 hover:text-blue-800 text-sm block"
                                        >
                                            What's the difference between an Inventory Assembly and a Group?
                                        </a>
                                    )}
                                    
                                    <a 
                                        href="#" 
                                        className="text-blue-600 hover:text-blue-800 text-sm block"
                                    >
                                        What is this cost?
                                    </a>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
