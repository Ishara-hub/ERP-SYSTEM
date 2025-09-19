import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { 
    ArrowLeft, 
    Edit, 
    Building, 
    Mail, 
    Phone, 
    Globe, 
    MapPin, 
    FileText, 
    CreditCard, 
    DollarSign,
    Package,
    Calendar,
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

interface Supplier {
    id: number;
    name: string;
    company_name: string;
    contact_person: string;
    email: string;
    phone: string;
    address: string;
    website: string;
    tax_id: string;
    payment_terms: string;
    credit_limit: number;
    currency: string;
    notes: string;
    is_active: boolean;
    supplier_code: string;
    created_at: string;
    purchase_orders: {
        id: number;
        po_number: string;
        order_date: string;
        status: string;
        total_amount: number;
        items: any[];
    }[];
}

interface Stats {
    total_orders: number;
    total_value: number;
    pending_orders: number;
    received_orders: number;
}

interface SupplierShowProps {
    supplier: Supplier;
    stats: Stats;
}

export default function SupplierShow({ supplier, stats }: SupplierShowProps) {
    const formatCurrency = (amount: number) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: supplier.currency || 'USD'
        }).format(amount);
    };

    const formatDate = (dateString: string) => {
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

    return (
        <AuthenticatedLayout>
            <Head title={`Supplier - ${supplier.name}`} />
            
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Link href="/suppliers">
                            <Button variant="outline" size="sm">
                                <ArrowLeft className="h-4 w-4 mr-2" />
                                Back to Suppliers
                            </Button>
                        </Link>
                        <div>
                            <h1 className="text-3xl font-bold tracking-tight">{supplier.name}</h1>
                            <p className="text-muted-foreground">
                                {supplier.company_name && supplier.company_name !== supplier.name && supplier.company_name}
                                {supplier.supplier_code && ` • ${supplier.supplier_code}`}
                            </p>
                        </div>
                    </div>
                    <div className="flex items-center gap-2">
                        <Badge className={supplier.is_active ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200'}>
                            {supplier.is_active ? 'ACTIVE' : 'INACTIVE'}
                        </Badge>
                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button variant="outline">
                                    Actions
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <DropdownMenuItem asChild>
                                    <Link href={`/suppliers/${supplier.id}/edit`}>
                                        <Edit className="h-4 w-4 mr-2" />
                                        Edit Supplier
                                    </Link>
                                </DropdownMenuItem>
                                <DropdownMenuItem asChild>
                                    <Link href={`/purchase-orders/create?supplier=${supplier.id}`}>
                                        <Plus className="h-4 w-4 mr-2" />
                                        Create Purchase Order
                                    </Link>
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </div>

                {/* Statistics Cards */}
                <div className="grid gap-4 md:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Orders</CardTitle>
                            <Package className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.total_orders}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Value</CardTitle>
                            <DollarSign className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{formatCurrency(stats.total_value)}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Pending Orders</CardTitle>
                            <Calendar className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.pending_orders}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Received Orders</CardTitle>
                            <Package className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.received_orders}</div>
                        </CardContent>
                    </Card>
                </div>

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
                                    <Hash className="h-4 w-4 text-muted-foreground" />
                                    <div>
                                        <div className="text-sm font-medium">Supplier Code</div>
                                        <div className="text-sm text-muted-foreground font-mono">{supplier.supplier_code}</div>
                                    </div>
                                </div>

                                {supplier.company_name && (
                                    <div className="flex items-center gap-3">
                                        <Building className="h-4 w-4 text-muted-foreground" />
                                        <div>
                                            <div className="text-sm font-medium">Company Name</div>
                                            <div className="text-sm text-muted-foreground">{supplier.company_name}</div>
                                        </div>
                                    </div>
                                )}

                                {supplier.contact_person && (
                                    <div className="flex items-center gap-3">
                                        <User className="h-4 w-4 text-muted-foreground" />
                                        <div>
                                            <div className="text-sm font-medium">Contact Person</div>
                                            <div className="text-sm text-muted-foreground">{supplier.contact_person}</div>
                                        </div>
                                    </div>
                                )}

                                {supplier.tax_id && (
                                    <div className="flex items-center gap-3">
                                        <FileText className="h-4 w-4 text-muted-foreground" />
                                        <div>
                                            <div className="text-sm font-medium">Tax ID</div>
                                            <div className="text-sm text-muted-foreground">{supplier.tax_id}</div>
                                        </div>
                                    </div>
                                )}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Contact Information */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Mail className="h-5 w-5" />
                                Contact Information
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-4">
                                <div className="flex items-center gap-3">
                                    <Mail className="h-4 w-4 text-muted-foreground" />
                                    <div>
                                        <div className="text-sm font-medium">Email</div>
                                        <div className="text-sm text-muted-foreground">
                                            <a href={`mailto:${supplier.email}`} className="text-blue-600 hover:underline">
                                                {supplier.email}
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div className="flex items-center gap-3">
                                    <Phone className="h-4 w-4 text-muted-foreground" />
                                    <div>
                                        <div className="text-sm font-medium">Phone</div>
                                        <div className="text-sm text-muted-foreground">
                                            <a href={`tel:${supplier.phone}`} className="text-blue-600 hover:underline">
                                                {supplier.phone}
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                {supplier.website && (
                                    <div className="flex items-center gap-3">
                                        <Globe className="h-4 w-4 text-muted-foreground" />
                                        <div>
                                            <div className="text-sm font-medium">Website</div>
                                            <div className="text-sm text-muted-foreground">
                                                <a href={supplier.website} target="_blank" rel="noopener noreferrer" className="text-blue-600 hover:underline">
                                                    {supplier.website}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                )}

                                <div className="flex items-start gap-3">
                                    <MapPin className="h-4 w-4 text-muted-foreground mt-1" />
                                    <div>
                                        <div className="text-sm font-medium">Address</div>
                                        <div className="text-sm text-muted-foreground whitespace-pre-line">{supplier.address}</div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Financial Information */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <CreditCard className="h-5 w-5" />
                            Financial Information
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4 md:grid-cols-3">
                            <div>
                                <div className="text-sm font-medium">Payment Terms</div>
                                <div className="text-sm text-muted-foreground">
                                    {supplier.payment_terms || 'Not specified'}
                                </div>
                            </div>
                            <div>
                                <div className="text-sm font-medium">Credit Limit</div>
                                <div className="text-sm text-muted-foreground">
                                    {supplier.credit_limit ? formatCurrency(supplier.credit_limit) : 'No limit set'}
                                </div>
                            </div>
                            <div>
                                <div className="text-sm font-medium">Currency</div>
                                <div className="text-sm text-muted-foreground">
                                    {supplier.currency || 'USD'}
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Notes */}
                {supplier.notes && (
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <FileText className="h-5 w-5" />
                                Notes
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-sm text-muted-foreground whitespace-pre-line">
                                {supplier.notes}
                            </div>
                        </CardContent>
                    </Card>
                )}

                {/* Recent Purchase Orders */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Package className="h-5 w-5" />
                            Recent Purchase Orders
                        </CardTitle>
                        <CardDescription>
                            Latest purchase orders from this supplier
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {supplier.purchase_orders.length > 0 ? (
                            <div className="space-y-4">
                                {supplier.purchase_orders.map((order) => (
                                    <div key={order.id} className="flex items-center justify-between p-4 border rounded-lg">
                                        <div className="space-y-1">
                                            <div className="font-medium">{order.po_number}</div>
                                            <div className="text-sm text-muted-foreground">
                                                {formatDate(order.order_date)} • {order.items.length} item{order.items.length !== 1 ? 's' : ''}
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-4">
                                            <div className="text-right">
                                                <div className="font-medium">{formatCurrency(order.total_amount)}</div>
                                                <Badge className={getStatusColor(order.status)}>
                                                    {getStatusLabel(order.status)}
                                                </Badge>
                                            </div>
                                            <Link href={`/purchase-orders/${order.id}`}>
                                                <Button variant="outline" size="sm">
                                                    View
                                                </Button>
                                            </Link>
                                        </div>
                                    </div>
                                ))}
                                <div className="text-center pt-4">
                                    <Link href={`/purchase-orders?supplier=${supplier.id}`}>
                                        <Button variant="outline">
                                            View All Orders
                                        </Button>
                                    </Link>
                                </div>
                            </div>
                        ) : (
                            <div className="text-center py-8 text-muted-foreground">
                                <Package className="h-12 w-12 mx-auto mb-4 opacity-50" />
                                <p>No purchase orders found for this supplier.</p>
                                <Link href={`/purchase-orders/create?supplier=${supplier.id}`}>
                                    <Button className="mt-4">
                                        <Plus className="h-4 w-4 mr-2" />
                                        Create First Order
                                    </Button>
                                </Link>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AuthenticatedLayout>
    );
}





