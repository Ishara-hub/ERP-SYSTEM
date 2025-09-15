import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { 
    ArrowLeft, 
    Edit, 
    Printer, 
    Building, 
    Calendar, 
    Package, 
    DollarSign,
    FileText,
    MapPin,
    User,
    Hash,
    Plus
} from 'lucide-react';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

interface PurchaseOrder {
    id: number;
    po_number: string;
    order_date: string;
    expected_delivery_date: string;
    actual_delivery_date: string;
    status: string;
    subtotal: number;
    tax_amount: number;
    shipping_amount: number;
    discount_amount: number;
    total_amount: number;
    shipping_address: string;
    billing_address: string;
    terms: string;
    reference: string;
    notes: string;
    created_by: string;
    approved_by: string;
    approved_at: string;
    created_at: string;
    supplier: {
        id: number;
        name: string;
        company_name: string;
        email: string;
        phone: string;
        address: string;
    };
    items: {
        id: number;
        description: string;
        quantity: number;
        unit_price: number;
        amount: number;
        received_quantity: number;
        tax_rate: number;
        tax_amount: number;
        unit_of_measure: string;
        notes: string;
        item: {
            id: number;
            item_name: string;
        } | null;
    }[];
}

interface PurchaseOrderShowProps {
    purchaseOrder: PurchaseOrder;
}

export default function PurchaseOrderShow({ purchaseOrder }: PurchaseOrderShowProps) {
    const formatCurrency = (amount: number) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(amount);
    };

    const formatDate = (dateString: string) => {
        if (!dateString) return 'Not set';
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    };

    const getStatusColor = (status: string) => {
        const colors: { [key: string]: string } = {
            'draft': 'bg-gray-50 text-gray-700 border-gray-200',
            'sent': 'bg-blue-50 text-blue-700 border-blue-200',
            'confirmed': 'bg-green-50 text-green-700 border-green-200',
            'partial': 'bg-yellow-50 text-yellow-700 border-yellow-200',
            'received': 'bg-green-50 text-green-700 border-green-200',
            'cancelled': 'bg-red-50 text-red-700 border-red-200',
        };
        return colors[status] || 'bg-gray-50 text-gray-700 border-gray-200';
    };

    const getStatusLabel = (status: string) => {
        return status.charAt(0).toUpperCase() + status.slice(1);
    };

    const getProgressPercentage = (status: string) => {
        const percentages: { [key: string]: number } = {
            'draft': 0,
            'sent': 25,
            'confirmed': 50,
            'partial': 75,
            'received': 100,
            'cancelled': 0,
        };
        return percentages[status] || 0;
    };

    const progressPercentage = getProgressPercentage(purchaseOrder.status);

    return (
        <AuthenticatedLayout>
            <Head title={`Purchase Order - ${purchaseOrder.po_number}`} />
            
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Link href="/purchase-orders">
                            <Button variant="outline" size="sm">
                                <ArrowLeft className="h-4 w-4 mr-2" />
                                Back to Purchase Orders
                            </Button>
                        </Link>
                        <div>
                            <h1 className="text-3xl font-bold tracking-tight">{purchaseOrder.po_number}</h1>
                            <p className="text-muted-foreground">
                                {purchaseOrder.supplier.company_name || purchaseOrder.supplier.name}
                                {purchaseOrder.reference && ` • ${purchaseOrder.reference}`}
                            </p>
                        </div>
                    </div>
                    <div className="flex items-center gap-2">
                        <Badge className={getStatusColor(purchaseOrder.status)}>
                            {getStatusLabel(purchaseOrder.status)}
                        </Badge>
                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button variant="outline">
                                    Actions
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <DropdownMenuItem asChild>
                                    <Link href={`/purchase-orders/${purchaseOrder.id}/edit`}>
                                        <Edit className="h-4 w-4 mr-2" />
                                        Edit Purchase Order
                                    </Link>
                                </DropdownMenuItem>
                                <DropdownMenuItem asChild>
                                    <Link href={`/purchase-orders/${purchaseOrder.id}/print`}>
                                        <Printer className="h-4 w-4 mr-2" />
                                        Print Purchase Order
                                    </Link>
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </div>

                {/* Progress Bar */}
                <Card>
                    <CardContent className="pt-6">
                        <div className="space-y-2">
                            <div className="flex justify-between text-sm">
                                <span>Order Progress</span>
                                <span>{progressPercentage}%</span>
                            </div>
                            <div className="w-full bg-gray-200 rounded-full h-2">
                                <div 
                                    className="bg-blue-600 h-2 rounded-full transition-all duration-300"
                                    style={{ width: `${progressPercentage}%` }}
                                ></div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <div className="grid gap-6 md:grid-cols-2">
                    {/* Supplier Information */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Building className="h-5 w-5" />
                                Supplier Information
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-4">
                                <div className="flex items-center gap-3">
                                    <Building className="h-4 w-4 text-muted-foreground" />
                                    <div>
                                        <div className="text-sm font-medium">{purchaseOrder.supplier.name}</div>
                                        {purchaseOrder.supplier.company_name && (
                                            <div className="text-sm text-muted-foreground">{purchaseOrder.supplier.company_name}</div>
                                        )}
                                    </div>
                                </div>

                                <div className="flex items-center gap-3">
                                    <Hash className="h-4 w-4 text-muted-foreground" />
                                    <div>
                                        <div className="text-sm font-medium">PO Number</div>
                                        <div className="text-sm text-muted-foreground font-mono">{purchaseOrder.po_number}</div>
                                    </div>
                                </div>

                                <div className="flex items-center gap-3">
                                    <Calendar className="h-4 w-4 text-muted-foreground" />
                                    <div>
                                        <div className="text-sm font-medium">Order Date</div>
                                        <div className="text-sm text-muted-foreground">{formatDate(purchaseOrder.order_date)}</div>
                                    </div>
                                </div>

                                <div className="flex items-center gap-3">
                                    <Package className="h-4 w-4 text-muted-foreground" />
                                    <div>
                                        <div className="text-sm font-medium">Expected Delivery</div>
                                        <div className="text-sm text-muted-foreground">{formatDate(purchaseOrder.expected_delivery_date)}</div>
                                    </div>
                                </div>

                                {purchaseOrder.actual_delivery_date && (
                                    <div className="flex items-center gap-3">
                                        <Package className="h-4 w-4 text-muted-foreground" />
                                        <div>
                                            <div className="text-sm font-medium">Actual Delivery</div>
                                            <div className="text-sm text-muted-foreground">{formatDate(purchaseOrder.actual_delivery_date)}</div>
                                        </div>
                                    </div>
                                )}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Order Details */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <FileText className="h-5 w-5" />
                                Order Details
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-4">
                                {purchaseOrder.reference && (
                                    <div className="flex items-center gap-3">
                                        <FileText className="h-4 w-4 text-muted-foreground" />
                                        <div>
                                            <div className="text-sm font-medium">Reference</div>
                                            <div className="text-sm text-muted-foreground">{purchaseOrder.reference}</div>
                                        </div>
                                    </div>
                                )}

                                {purchaseOrder.terms && (
                                    <div className="flex items-center gap-3">
                                        <FileText className="h-4 w-4 text-muted-foreground" />
                                        <div>
                                            <div className="text-sm font-medium">Payment Terms</div>
                                            <div className="text-sm text-muted-foreground">{purchaseOrder.terms}</div>
                                        </div>
                                    </div>
                                )}

                                {purchaseOrder.created_by && (
                                    <div className="flex items-center gap-3">
                                        <User className="h-4 w-4 text-muted-foreground" />
                                        <div>
                                            <div className="text-sm font-medium">Created By</div>
                                            <div className="text-sm text-muted-foreground">{purchaseOrder.created_by}</div>
                                        </div>
                                    </div>
                                )}

                                {purchaseOrder.approved_by && (
                                    <div className="flex items-center gap-3">
                                        <User className="h-4 w-4 text-muted-foreground" />
                                        <div>
                                            <div className="text-sm font-medium">Approved By</div>
                                            <div className="text-sm text-muted-foreground">{purchaseOrder.approved_by}</div>
                                        </div>
                                    </div>
                                )}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Addresses */}
                {(purchaseOrder.shipping_address || purchaseOrder.billing_address) && (
                    <div className="grid gap-6 md:grid-cols-2">
                        {purchaseOrder.shipping_address && (
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <MapPin className="h-5 w-5" />
                                        Shipping Address
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="text-sm text-muted-foreground whitespace-pre-line">
                                        {purchaseOrder.shipping_address}
                                    </div>
                                </CardContent>
                            </Card>
                        )}

                        {purchaseOrder.billing_address && (
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <MapPin className="h-5 w-5" />
                                        Billing Address
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="text-sm text-muted-foreground whitespace-pre-line">
                                        {purchaseOrder.billing_address}
                                    </div>
                                </CardContent>
                            </Card>
                        )}
                    </div>
                )}

                {/* Line Items */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Package className="h-5 w-5" />
                            Line Items
                        </CardTitle>
                        <CardDescription>
                            {purchaseOrder.items.length} item{purchaseOrder.items.length !== 1 ? 's' : ''} in this order
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead>
                                    <tr className="border-b">
                                        <th className="text-left p-4">Description</th>
                                        <th className="text-left p-4">Quantity</th>
                                        <th className="text-left p-4">Unit Price</th>
                                        <th className="text-left p-4">Tax Rate</th>
                                        <th className="text-left p-4">Amount</th>
                                        <th className="text-left p-4">Received</th>
                                        <th className="text-left p-4">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {purchaseOrder.items.map((item) => (
                                        <tr key={item.id} className="border-b">
                                            <td className="p-4">
                                                <div>
                                                    <div className="font-medium">{item.description}</div>
                                                    {item.item && (
                                                        <div className="text-sm text-muted-foreground">
                                                            Item: {item.item.item_name}
                                                        </div>
                                                    )}
                                                    {item.unit_of_measure && (
                                                        <div className="text-sm text-muted-foreground">
                                                            Unit: {item.unit_of_measure}
                                                        </div>
                                                    )}
                                                </div>
                                            </td>
                                            <td className="p-4">
                                                <div className="text-center">
                                                    <div className="font-medium">{item.quantity}</div>
                                                </div>
                                            </td>
                                            <td className="p-4">
                                                <div className="font-medium">{formatCurrency(item.unit_price)}</div>
                                            </td>
                                            <td className="p-4">
                                                <div className="text-center">
                                                    <div>{item.tax_rate}%</div>
                                                </div>
                                            </td>
                                            <td className="p-4">
                                                <div className="font-medium">{formatCurrency(item.amount)}</div>
                                                {item.tax_amount > 0 && (
                                                    <div className="text-sm text-muted-foreground">
                                                        +{formatCurrency(item.tax_amount)} tax
                                                    </div>
                                                )}
                                            </td>
                                            <td className="p-4">
                                                <div className="text-center">
                                                    <div className="font-medium">{item.received_quantity}</div>
                                                    <div className="text-sm text-muted-foreground">
                                                        of {item.quantity}
                                                    </div>
                                                </div>
                                            </td>
                                            <td className="p-4">
                                                <Badge className={
                                                    item.received_quantity >= item.quantity 
                                                        ? 'bg-green-50 text-green-700 border-green-200'
                                                        : item.received_quantity > 0
                                                        ? 'bg-yellow-50 text-yellow-700 border-yellow-200'
                                                        : 'bg-gray-50 text-gray-700 border-gray-200'
                                                }>
                                                    {item.received_quantity >= item.quantity 
                                                        ? 'Received' 
                                                        : item.received_quantity > 0 
                                                        ? 'Partial' 
                                                        : 'Pending'
                                                    }
                                                </Badge>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>

                {/* Order Summary */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <DollarSign className="h-5 w-5" />
                            Order Summary
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-2">
                            <div className="flex justify-between">
                                <span>Subtotal:</span>
                                <span>{formatCurrency(purchaseOrder.subtotal)}</span>
                            </div>
                            <div className="flex justify-between">
                                <span>Tax Amount:</span>
                                <span>{formatCurrency(purchaseOrder.tax_amount)}</span>
                            </div>
                            <div className="flex justify-between">
                                <span>Shipping:</span>
                                <span>{formatCurrency(purchaseOrder.shipping_amount)}</span>
                            </div>
                            <div className="flex justify-between">
                                <span>Discount:</span>
                                <span>-{formatCurrency(purchaseOrder.discount_amount)}</span>
                            </div>
                            <div className="flex justify-between font-bold text-lg border-t pt-2">
                                <span>Total:</span>
                                <span>{formatCurrency(purchaseOrder.total_amount)}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Notes */}
                {purchaseOrder.notes && (
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <FileText className="h-5 w-5" />
                                Notes
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-sm text-muted-foreground whitespace-pre-line">
                                {purchaseOrder.notes}
                            </div>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AuthenticatedLayout>
    );
}




