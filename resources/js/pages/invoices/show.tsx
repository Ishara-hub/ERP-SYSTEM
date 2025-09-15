import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { 
    ArrowLeft, 
    Edit, 
    Printer, 
    Mail, 
    DollarSign,
    Calendar,
    User,
    Package,
    FileText,
    CheckCircle
} from 'lucide-react';

interface Invoice {
    id: number;
    invoice_no: string;
    date: string;
    ship_date: string;
    total_amount: number;
    subtotal: number;
    tax_amount: number;
    discount_amount: number;
    shipping_amount: number;
    payments_applied: number;
    balance_due: number;
    status: string;
    po_number: string;
    terms: string;
    rep: string;
    via: string;
    fob: string;
    customer_message: string;
    memo: string;
    billing_address: string;
    shipping_address: string;
    template: string;
    is_online_payment_enabled: boolean;
    created_at: string;
    updated_at: string;
    customer: {
        id: number;
        name: string;
        email: string;
        phone: string;
        address: string;
    };
    line_items: Array<{
        id: number;
        description: string;
        quantity: number;
        unit_price: number;
        amount: number;
        tax_rate: number;
        tax_amount: number;
        item?: {
            id: number;
            item_name: string;
            item_type: string;
        };
    }>;
    payments: Array<{
        id: number;
        payment_date: string;
        payment_method: string;
        amount: number;
        reference: string;
    }>;
}

interface InvoiceShowProps {
    invoice: Invoice;
}

export default function InvoiceShow({ invoice }: InvoiceShowProps) {
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
                return 'bg-green-50 text-green-700 border-green-200';
            case 'unpaid':
                return 'bg-red-50 text-red-700 border-red-200';
            case 'partial':
                return 'bg-yellow-50 text-yellow-700 border-yellow-200';
            default:
                return 'bg-gray-50 text-gray-700 border-gray-200';
        }
    };

    const handleMarkAsPaid = () => {
        router.patch(`/invoices/${invoice.id}/mark-paid`, {}, {
            onSuccess: () => {
                // The page will refresh with updated data
            }
        });
    };

    return (
        <AuthenticatedLayout>
            <Head title={`Invoice: ${invoice.invoice_no}`} />
            
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Link href="/invoices">
                            <Button variant="outline" size="sm">
                                <ArrowLeft className="h-4 w-4 mr-2" />
                                Back to Invoices
                            </Button>
                        </Link>
                        <div>
                            <h1 className="text-3xl font-bold tracking-tight">Invoice {invoice.invoice_no}</h1>
                            <p className="text-muted-foreground">
                                Invoice Details
                            </p>
                        </div>
                    </div>
                    <div className="flex items-center gap-2">
                        <Badge className={getStatusColor(invoice.status)}>
                            {invoice.status.toUpperCase()}
                        </Badge>
                        <Link href={`/invoices/${invoice.id}/edit`}>
                            <Button variant="outline">
                                <Edit className="h-4 w-4 mr-2" />
                                Edit
                            </Button>
                        </Link>
                        <Link href={`/invoices/${invoice.id}/print`}>
                            <Button variant="outline">
                                <Printer className="h-4 w-4 mr-2" />
                                Print
                            </Button>
                        </Link>
                        <Button variant="outline" onClick={() => router.post(`/invoices/${invoice.id}/email`)}>
                            <Mail className="h-4 w-4 mr-2" />
                            Email
                        </Button>
                    </div>
                </div>

                <div className="grid gap-6 lg:grid-cols-3">
                    {/* Main Content */}
                    <div className="lg:col-span-2 space-y-6">
                        {/* Invoice Header */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <FileText className="h-5 w-5" />
                                    Invoice Information
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid gap-4 md:grid-cols-2">
                                    <div className="space-y-2">
                                        <div className="text-sm font-medium text-muted-foreground">Invoice Number</div>
                                        <div className="text-lg font-semibold">{invoice.invoice_no}</div>
                                    </div>
                                    <div className="space-y-2">
                                        <div className="text-sm font-medium text-muted-foreground">Date</div>
                                        <div className="flex items-center gap-2">
                                            <Calendar className="h-4 w-4 text-muted-foreground" />
                                            {formatDate(invoice.date)}
                                        </div>
                                    </div>
                                </div>

                                {invoice.po_number && (
                                    <div className="space-y-2">
                                        <div className="text-sm font-medium text-muted-foreground">P.O. Number</div>
                                        <div>{invoice.po_number}</div>
                                    </div>
                                )}

                                {invoice.terms && (
                                    <div className="space-y-2">
                                        <div className="text-sm font-medium text-muted-foreground">Terms</div>
                                        <div>{invoice.terms}</div>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        {/* Customer Information */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <User className="h-5 w-5" />
                                    Customer Information
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div>
                                    <div className="text-lg font-semibold">{invoice.customer.name}</div>
                                    <div className="text-muted-foreground">{invoice.customer.email}</div>
                                    <div className="text-muted-foreground">{invoice.customer.phone}</div>
                                </div>

                                {invoice.billing_address && (
                                    <div>
                                        <div className="text-sm font-medium text-muted-foreground mb-2">Billing Address</div>
                                        <div className="whitespace-pre-line">{invoice.billing_address}</div>
                                    </div>
                                )}

                                {invoice.shipping_address && invoice.shipping_address !== invoice.billing_address && (
                                    <div>
                                        <div className="text-sm font-medium text-muted-foreground mb-2">Shipping Address</div>
                                        <div className="whitespace-pre-line">{invoice.shipping_address}</div>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        {/* Line Items */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Line Items</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="overflow-x-auto">
                                    <table className="w-full">
                                        <thead>
                                            <tr className="border-b">
                                                <th className="text-left p-2">Description</th>
                                                <th className="text-right p-2">Qty</th>
                                                <th className="text-right p-2">Unit Price</th>
                                                <th className="text-right p-2">Tax Rate</th>
                                                <th className="text-right p-2">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {invoice.line_items.map((item) => (
                                                <tr key={item.id} className="border-b">
                                                    <td className="p-2">
                                                        <div className="font-medium">{item.description}</div>
                                                        {item.item && (
                                                            <div className="text-sm text-muted-foreground">
                                                                {item.item.item_type}
                                                            </div>
                                                        )}
                                                    </td>
                                                    <td className="p-2 text-right">{item.quantity}</td>
                                                    <td className="p-2 text-right">{formatCurrency(item.unit_price)}</td>
                                                    <td className="p-2 text-right">{item.tax_rate}%</td>
                                                    <td className="p-2 text-right font-medium">{formatCurrency(item.amount)}</td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Additional Information */}
                        {(invoice.memo || invoice.customer_message) && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>Additional Information</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    {invoice.customer_message && (
                                        <div>
                                            <div className="text-sm font-medium text-muted-foreground mb-2">Customer Message</div>
                                            <div>{invoice.customer_message}</div>
                                        </div>
                                    )}
                                    {invoice.memo && (
                                        <div>
                                            <div className="text-sm font-medium text-muted-foreground mb-2">Memo</div>
                                            <div>{invoice.memo}</div>
                                        </div>
                                    )}
                                </CardContent>
                            </Card>
                        )}

                        {/* Payments */}
                        {invoice.payments.length > 0 && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>Payment History</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-3">
                                        {invoice.payments.map((payment) => (
                                            <div key={payment.id} className="flex items-center justify-between p-3 border rounded-lg">
                                                <div>
                                                    <div className="font-medium">{payment.payment_method}</div>
                                                    <div className="text-sm text-muted-foreground">
                                                        {formatDate(payment.payment_date)}
                                                    </div>
                                                    {payment.reference && (
                                                        <div className="text-sm text-muted-foreground">
                                                            Ref: {payment.reference}
                                                        </div>
                                                    )}
                                                </div>
                                                <div className="text-right">
                                                    <div className="font-medium">{formatCurrency(payment.amount)}</div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </CardContent>
                            </Card>
                        )}
                    </div>

                    {/* Sidebar */}
                    <div className="space-y-4">
                        {/* Invoice Summary */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Invoice Summary</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3">
                                <div className="flex justify-between">
                                    <span>Subtotal:</span>
                                    <span>{formatCurrency(invoice.subtotal)}</span>
                                </div>
                                {invoice.tax_amount > 0 && (
                                    <div className="flex justify-between">
                                        <span>Tax:</span>
                                        <span>{formatCurrency(invoice.tax_amount)}</span>
                                    </div>
                                )}
                                {invoice.discount_amount > 0 && (
                                    <div className="flex justify-between">
                                        <span>Discount:</span>
                                        <span>-{formatCurrency(invoice.discount_amount)}</span>
                                    </div>
                                )}
                                {invoice.shipping_amount > 0 && (
                                    <div className="flex justify-between">
                                        <span>Shipping:</span>
                                        <span>{formatCurrency(invoice.shipping_amount)}</span>
                                    </div>
                                )}
                                <div className="border-t pt-3">
                                    <div className="flex justify-between font-bold text-lg">
                                        <span>Total:</span>
                                        <span>{formatCurrency(invoice.total_amount)}</span>
                                    </div>
                                </div>
                                {invoice.payments_applied > 0 && (
                                    <div className="flex justify-between text-green-600">
                                        <span>Payments Applied:</span>
                                        <span>{formatCurrency(invoice.payments_applied)}</span>
                                    </div>
                                )}
                                <div className="border-t pt-3">
                                    <div className="flex justify-between font-bold text-lg">
                                        <span>Balance Due:</span>
                                        <span className={invoice.balance_due > 0 ? 'text-red-600' : 'text-green-600'}>
                                            {formatCurrency(invoice.balance_due)}
                                        </span>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Actions */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Actions</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-2">
                                {invoice.status !== 'paid' && (
                                    <Button 
                                        onClick={handleMarkAsPaid}
                                        className="w-full"
                                        variant="outline"
                                    >
                                        <CheckCircle className="h-4 w-4 mr-2" />
                                        Mark as Paid
                                    </Button>
                                )}
                                
                                <Link href={`/invoices/${invoice.id}/print`} className="block">
                                    <Button variant="outline" className="w-full">
                                        <Printer className="h-4 w-4 mr-2" />
                                        Print Invoice
                                    </Button>
                                </Link>
                                
                                <Button 
                                    variant="outline" 
                                    className="w-full"
                                    onClick={() => router.post(`/invoices/${invoice.id}/email`)}
                                >
                                    <Mail className="h-4 w-4 mr-2" />
                                    Email Invoice
                                </Button>
                                
                                {invoice.status !== 'paid' && (
                                    <Link href={`/invoices/${invoice.id}/receive-payment`} className="block">
                                        <Button variant="outline" className="w-full">
                                            <DollarSign className="h-4 w-4 mr-2" />
                                            Receive Payment
                                        </Button>
                                    </Link>
                                )}
                            </CardContent>
                        </Card>

                        {/* Invoice Info */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Invoice Info</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-2 text-sm text-muted-foreground">
                                <div className="flex items-center gap-2">
                                    <Calendar className="h-4 w-4" />
                                    <span>Created: {formatDate(invoice.created_at)}</span>
                                </div>
                                <div className="flex items-center gap-2">
                                    <Calendar className="h-4 w-4" />
                                    <span>Updated: {formatDate(invoice.updated_at)}</span>
                                </div>
                                {invoice.ship_date && (
                                    <div className="flex items-center gap-2">
                                        <Package className="h-4 w-4" />
                                        <span>Ship Date: {formatDate(invoice.ship_date)}</span>
                                    </div>
                                )}
                                {invoice.is_online_payment_enabled && (
                                    <div className="flex items-center gap-2">
                                        <DollarSign className="h-4 w-4" />
                                        <span>Online Payment Enabled</span>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
