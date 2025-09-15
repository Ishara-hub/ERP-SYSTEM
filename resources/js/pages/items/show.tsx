import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { 
    ArrowLeft, 
    Edit, 
    Package, 
    Wrench, 
    Settings,
    DollarSign,
    Building,
    Mail,
    Phone,
    MapPin
} from 'lucide-react';

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
    purchase_description: string | null;
    sales_description: string | null;
    reorder_point: number | string | null;
    max_quantity: number | string | null;
    notes: string | null;
    parent?: {
        id: number;
        item_name: string;
    };
    cogs_account?: {
        account_name: string;
    };
    income_account?: {
        account_name: string;
    };
    asset_account?: {
        account_name: string;
    };
    preferred_vendor?: {
        name: string;
    };
    children_count: number;
    created_at: string;
}

interface ItemShowProps {
    item: Item;
}

export default function ItemShow({ item }: ItemShowProps) {
    const getItemTypeIcon = (type: string) => {
        switch (type) {
            case 'Service':
                return <Wrench className="h-5 w-5" />;
            case 'Inventory Part':
            case 'Inventory Assembly':
                return <Package className="h-5 w-5" />;
            default:
                return <Settings className="h-5 w-5" />;
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

    const formatCurrency = (value: number | string) => {
        const numValue = typeof value === 'number' ? value : parseFloat(value) || 0;
        return numValue.toFixed(2);
    };

    const formatNumber = (value: number | string) => {
        const numValue = typeof value === 'number' ? value : parseFloat(value) || 0;
        return numValue.toFixed(2);
    };

    return (
        <AuthenticatedLayout>
            <Head title={`Item: ${item.item_name}`} />
            
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
                            <h1 className="text-3xl font-bold tracking-tight">{item.item_name}</h1>
                            <p className="text-muted-foreground">
                                {item.item_number && `Item #${item.item_number}`}
                            </p>
                        </div>
                    </div>
                    <div className="flex items-center gap-2">
                        <Link href={`/items/${item.id}/edit`}>
                            <Button>
                                <Edit className="h-4 w-4 mr-2" />
                                Edit Item
                            </Button>
                        </Link>
                    </div>
                </div>

                {/* Status and Type */}
                <div className="flex items-center gap-4">
                    <Badge className={getItemTypeColor(item.item_type)}>
                        <div className="flex items-center gap-2">
                            {getItemTypeIcon(item.item_type)}
                            {item.item_type}
                        </div>
                    </Badge>
                    <Badge variant={item.is_active && !item.is_inactive ? "default" : "destructive"}>
                        {item.is_active && !item.is_inactive ? 'Active' : 'Inactive'}
                    </Badge>
                </div>

                <div className="grid gap-6 md:grid-cols-2">
                    {/* Basic Information */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                {getItemTypeIcon(item.item_type)}
                                Basic Information
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div>
                                <label className="text-sm font-medium text-muted-foreground">Item Name</label>
                                <p className="text-lg">{item.item_name}</p>
                            </div>
                            {item.item_number && (
                                <div>
                                    <label className="text-sm font-medium text-muted-foreground">Item Number</label>
                                    <p className="text-lg">{item.item_number}</p>
                                </div>
                            )}
                            {item.manufacturer_part_number && (
                                <div>
                                    <label className="text-sm font-medium text-muted-foreground">Manufacturer Part Number</label>
                                    <p className="text-lg">{item.manufacturer_part_number}</p>
                                </div>
                            )}
                            {item.unit_of_measure && (
                                <div>
                                    <label className="text-sm font-medium text-muted-foreground">Unit of Measure</label>
                                    <p className="text-lg">{item.unit_of_measure}</p>
                                </div>
                            )}
                            {item.parent && (
                                <div>
                                    <label className="text-sm font-medium text-muted-foreground">Parent Item</label>
                                    <p className="text-lg">{item.parent.item_name}</p>
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    {/* Pricing Information */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <DollarSign className="h-5 w-5" />
                                Pricing Information
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div>
                                <label className="text-sm font-medium text-muted-foreground">Cost</label>
                                <p className="text-lg font-semibold">${formatCurrency(item.cost)}</p>
                            </div>
                            <div>
                                <label className="text-sm font-medium text-muted-foreground">Sales Price</label>
                                <p className="text-lg font-semibold">${formatCurrency(item.sales_price)}</p>
                            </div>
                            {item.item_type.includes('Inventory') && (
                                <>
                                    <div>
                                        <label className="text-sm font-medium text-muted-foreground">On Hand</label>
                                        <p className="text-lg">{formatNumber(item.on_hand)}</p>
                                    </div>
                                    <div>
                                        <label className="text-sm font-medium text-muted-foreground">Total Value</label>
                                        <p className="text-lg font-semibold">${formatCurrency(item.total_value)}</p>
                                    </div>
                                </>
                            )}
                        </CardContent>
                    </Card>

                    {/* Account Information */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Account Information</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            {item.cogs_account && (
                                <div>
                                    <label className="text-sm font-medium text-muted-foreground">COGS Account</label>
                                    <p className="text-lg">{item.cogs_account.account_name}</p>
                                </div>
                            )}
                            {item.income_account && (
                                <div>
                                    <label className="text-sm font-medium text-muted-foreground">Income Account</label>
                                    <p className="text-lg">{item.income_account.account_name}</p>
                                </div>
                            )}
                            {item.asset_account && (
                                <div>
                                    <label className="text-sm font-medium text-muted-foreground">Asset Account</label>
                                    <p className="text-lg">{item.asset_account.account_name}</p>
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    {/* Vendor Information */}
                    {item.preferred_vendor && (
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Building className="h-5 w-5" />
                                    Preferred Vendor
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div>
                                    <label className="text-sm font-medium text-muted-foreground">Vendor Name</label>
                                    <p className="text-lg">{item.preferred_vendor.name}</p>
                                </div>
                            </CardContent>
                        </Card>
                    )}
                </div>

                {/* Descriptions */}
                {(item.purchase_description || item.sales_description) && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Descriptions</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            {item.purchase_description && (
                                <div>
                                    <label className="text-sm font-medium text-muted-foreground">Purchase Description</label>
                                    <p className="text-sm whitespace-pre-wrap">{item.purchase_description}</p>
                                </div>
                            )}
                            {item.sales_description && (
                                <div>
                                    <label className="text-sm font-medium text-muted-foreground">Sales Description</label>
                                    <p className="text-sm whitespace-pre-wrap">{item.sales_description}</p>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                )}

                {/* Notes */}
                {item.notes && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Notes</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p className="text-sm whitespace-pre-wrap">{item.notes}</p>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
