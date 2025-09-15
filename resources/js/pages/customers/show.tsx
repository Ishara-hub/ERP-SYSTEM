import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { 
    ArrowLeft, 
    Edit, 
    Mail, 
    Phone, 
    MapPin, 
    Calendar,
    FileText,
    ShoppingCart,
    MessageSquare,
    DollarSign
} from 'lucide-react';

interface Customer {
    id: number;
    name: string;
    email: string;
    phone: string;
    address: string;
    created_at: string;
    updated_at: string;
    invoices?: Invoice[];
    sales_orders?: SalesOrder[];
    interactions?: Interaction[];
}

interface Invoice {
    id: number;
    invoice_no: string;
    date: string;
    total_amount: number;
    status: string;
}

interface SalesOrder {
    id: number;
    so_no: string;
    date: string;
    total_amount: number;
    status: string;
}

interface Interaction {
    id: number;
    date: string;
    type: string;
    notes: string;
}

interface CustomerShowProps {
    customer: Customer;
}

export default function CustomerShow({ customer }: CustomerShowProps) {
    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    };

    const formatCurrency = (amount: number) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(amount);
    };

    const getStatusColor = (status: string) => {
        switch (status.toLowerCase()) {
            case 'paid':
            case 'completed':
                return 'bg-green-50 text-green-700 border-green-200';
            case 'pending':
            case 'processing':
                return 'bg-yellow-50 text-yellow-700 border-yellow-200';
            case 'overdue':
            case 'cancelled':
                return 'bg-red-50 text-red-700 border-red-200';
            default:
                return 'bg-gray-50 text-gray-700 border-gray-200';
        }
    };

    const totalInvoices = customer.invoices?.length || 0;
    const totalSalesOrders = customer.sales_orders?.length || 0;
    const totalInteractions = customer.interactions?.length || 0;

    const totalInvoiceAmount = customer.invoices?.reduce((sum, invoice) => sum + invoice.total_amount, 0) || 0;
    const totalSalesOrderAmount = customer.sales_orders?.reduce((sum, order) => sum + order.total_amount, 0) || 0;

    return (
        <AuthenticatedLayout>
            <Head title={`Customer: ${customer.name}`} />
            
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Link href="/customers">
                            <Button variant="outline" size="sm">
                                <ArrowLeft className="h-4 w-4 mr-2" />
                                Back to Customers
                            </Button>
                        </Link>
                        <div>
                            <h1 className="text-3xl font-bold tracking-tight">{customer.name}</h1>
                            <p className="text-muted-foreground">
                                Customer Details
                            </p>
                        </div>
                    </div>
                    <Link href={`/customers/${customer.id}/edit`}>
                        <Button>
                            <Edit className="h-4 w-4 mr-2" />
                            Edit Customer
                        </Button>
                    </Link>
                </div>

                <div className="grid gap-6 lg:grid-cols-3">
                    {/* Main Content */}
                    <div className="lg:col-span-2 space-y-6">
                        {/* Contact Information */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Contact Information</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="flex items-center gap-3">
                                    <Mail className="h-5 w-5 text-muted-foreground" />
                                    <div>
                                        <div className="font-medium">Email</div>
                                        <div className="text-muted-foreground">{customer.email}</div>
                                    </div>
                                </div>
                                
                                <div className="flex items-center gap-3">
                                    <Phone className="h-5 w-5 text-muted-foreground" />
                                    <div>
                                        <div className="font-medium">Phone</div>
                                        <div className="text-muted-foreground">{customer.phone}</div>
                                    </div>
                                </div>
                                
                                <div className="flex items-start gap-3">
                                    <MapPin className="h-5 w-5 text-muted-foreground mt-1" />
                                    <div>
                                        <div className="font-medium">Address</div>
                                        <div className="text-muted-foreground whitespace-pre-line">{customer.address}</div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Recent Invoices */}
                        {totalInvoices > 0 && (
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <FileText className="h-5 w-5" />
                                        Recent Invoices
                                    </CardTitle>
                                    <CardDescription>
                                        {totalInvoices} invoice{totalInvoices !== 1 ? 's' : ''} • Total: {formatCurrency(totalInvoiceAmount)}
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-3">
                                        {customer.invoices?.slice(0, 5).map((invoice) => (
                                            <div key={invoice.id} className="flex items-center justify-between p-3 border rounded-lg">
                                                <div>
                                                    <div className="font-medium">{invoice.invoice_no}</div>
                                                    <div className="text-sm text-muted-foreground">
                                                        {formatDate(invoice.date)}
                                                    </div>
                                                </div>
                                                <div className="text-right">
                                                    <div className="font-medium">{formatCurrency(invoice.total_amount)}</div>
                                                    <Badge className={getStatusColor(invoice.status)}>
                                                        {invoice.status}
                                                    </Badge>
                                                </div>
                                            </div>
                                        ))}
                                        {totalInvoices > 5 && (
                                            <div className="text-center text-sm text-muted-foreground">
                                                And {totalInvoices - 5} more invoice{totalInvoices - 5 !== 1 ? 's' : ''}...
                                            </div>
                                        )}
                                    </div>
                                </CardContent>
                            </Card>
                        )}

                        {/* Recent Sales Orders */}
                        {totalSalesOrders > 0 && (
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <ShoppingCart className="h-5 w-5" />
                                        Recent Sales Orders
                                    </CardTitle>
                                    <CardDescription>
                                        {totalSalesOrders} order{totalSalesOrders !== 1 ? 's' : ''} • Total: {formatCurrency(totalSalesOrderAmount)}
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-3">
                                        {customer.sales_orders?.slice(0, 5).map((order) => (
                                            <div key={order.id} className="flex items-center justify-between p-3 border rounded-lg">
                                                <div>
                                                    <div className="font-medium">{order.so_no}</div>
                                                    <div className="text-sm text-muted-foreground">
                                                        {formatDate(order.date)}
                                                    </div>
                                                </div>
                                                <div className="text-right">
                                                    <div className="font-medium">{formatCurrency(order.total_amount)}</div>
                                                    <Badge className={getStatusColor(order.status)}>
                                                        {order.status}
                                                    </Badge>
                                                </div>
                                            </div>
                                        ))}
                                        {totalSalesOrders > 5 && (
                                            <div className="text-center text-sm text-muted-foreground">
                                                And {totalSalesOrders - 5} more order{totalSalesOrders - 5 !== 1 ? 's' : ''}...
                                            </div>
                                        )}
                                    </div>
                                </CardContent>
                            </Card>
                        )}

                        {/* Recent Interactions */}
                        {totalInteractions > 0 && (
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <MessageSquare className="h-5 w-5" />
                                        Recent Interactions
                                    </CardTitle>
                                    <CardDescription>
                                        {totalInteractions} interaction{totalInteractions !== 1 ? 's' : ''}
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-3">
                                        {customer.interactions?.slice(0, 5).map((interaction) => (
                                            <div key={interaction.id} className="p-3 border rounded-lg">
                                                <div className="flex items-center justify-between mb-2">
                                                    <Badge variant="outline">{interaction.type}</Badge>
                                                    <div className="text-sm text-muted-foreground">
                                                        {formatDate(interaction.date)}
                                                    </div>
                                                </div>
                                                <div className="text-sm">{interaction.notes}</div>
                                            </div>
                                        ))}
                                        {totalInteractions > 5 && (
                                            <div className="text-center text-sm text-muted-foreground">
                                                And {totalInteractions - 5} more interaction{totalInteractions - 5 !== 1 ? 's' : ''}...
                                            </div>
                                        )}
                                    </div>
                                </CardContent>
                            </Card>
                        )}
                    </div>

                    {/* Sidebar */}
                    <div className="space-y-4">
                        {/* Summary Stats */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Summary</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-2">
                                        <FileText className="h-4 w-4 text-muted-foreground" />
                                        <span className="text-sm">Invoices</span>
                                    </div>
                                    <div className="font-medium">{totalInvoices}</div>
                                </div>
                                
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-2">
                                        <ShoppingCart className="h-4 w-4 text-muted-foreground" />
                                        <span className="text-sm">Sales Orders</span>
                                    </div>
                                    <div className="font-medium">{totalSalesOrders}</div>
                                </div>
                                
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-2">
                                        <MessageSquare className="h-4 w-4 text-muted-foreground" />
                                        <span className="text-sm">Interactions</span>
                                    </div>
                                    <div className="font-medium">{totalInteractions}</div>
                                </div>
                                
                                <div className="border-t pt-4">
                                    <div className="flex items-center justify-between">
                                        <div className="flex items-center gap-2">
                                            <DollarSign className="h-4 w-4 text-muted-foreground" />
                                            <span className="text-sm font-medium">Total Value</span>
                                        </div>
                                        <div className="font-medium">{formatCurrency(totalInvoiceAmount + totalSalesOrderAmount)}</div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Customer Info */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Customer Info</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3 text-sm">
                                <div className="flex items-center gap-2">
                                    <Calendar className="h-4 w-4 text-muted-foreground" />
                                    <span className="text-muted-foreground">Created:</span>
                                    <span>{formatDate(customer.created_at)}</span>
                                </div>
                                
                                <div className="flex items-center gap-2">
                                    <Calendar className="h-4 w-4 text-muted-foreground" />
                                    <span className="text-muted-foreground">Updated:</span>
                                    <span>{formatDate(customer.updated_at)}</span>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
