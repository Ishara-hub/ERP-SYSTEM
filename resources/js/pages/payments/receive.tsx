import { useState, useEffect } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { 
    ArrowLeft, 
    Save, 
    X, 
    DollarSign,
    Calendar,
    CreditCard,
    Receipt,
    Building,
    FileText,
    User
} from 'lucide-react';

interface Invoice {
    id: number;
    invoice_no: string;
    date: string;
    total_amount: number;
    balance_due: number;
    status: string;
    customer: {
        id: number;
        name: string;
        email: string;
        phone: string;
    };
}

interface ReceivePaymentProps {
    invoice: Invoice;
}

const PAYMENT_METHODS = [
    { value: 'cash', label: 'Cash', icon: DollarSign },
    { value: 'check', label: 'Check', icon: Receipt },
    { value: 'credit card', label: 'Credit Card', icon: CreditCard },
    { value: 'bank transfer', label: 'Bank Transfer', icon: Building },
    { value: 'online', label: 'Online Payment', icon: CreditCard },
];

export default function ReceivePayment({ invoice }: ReceivePaymentProps) {
    const { data, setData, post, processing, errors } = useForm({
        payment_date: new Date().toISOString().split('T')[0],
        payment_method: 'none',
        amount: invoice.balance_due.toString(),
        reference: '',
        notes: '',
        bank_name: '',
        check_number: '',
        transaction_id: '',
        fee_amount: '0',
        received_by: '',
    });

    const [netAmount, setNetAmount] = useState(invoice.balance_due);

    // Calculate net amount when amount or fee changes
    useEffect(() => {
        const amount = parseFloat(data.amount) || 0;
        const fee = parseFloat(data.fee_amount) || 0;
        setNetAmount(amount - fee);
    }, [data.amount, data.fee_amount]);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        
        // Process form data to convert "none" values to empty strings
        const processedData = {
            ...data,
            payment_method: data.payment_method === 'none' ? '' : data.payment_method,
        };
        
        // Update form data with processed values
        Object.keys(processedData).forEach(key => {
            setData(key as keyof typeof data, processedData[key as keyof typeof processedData]);
        });
        
        post(`/invoices/${invoice.id}/receive-payment`);
    };

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

    return (
        <AuthenticatedLayout>
            <Head title={`Receive Payment - Invoice ${invoice.invoice_no}`} />
            
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Link href={`/invoices/${invoice.id}`}>
                            <Button variant="outline" size="sm">
                                <ArrowLeft className="h-4 w-4 mr-2" />
                                Back to Invoice
                            </Button>
                        </Link>
                        <div>
                            <h1 className="text-3xl font-bold tracking-tight">Receive Payment</h1>
                            <p className="text-muted-foreground">
                                Record payment for invoice {invoice.invoice_no}
                            </p>
                        </div>
                    </div>
                    <Badge className={getStatusColor(invoice.status)}>
                        {invoice.status.toUpperCase()}
                    </Badge>
                </div>

                <div className="grid gap-6 lg:grid-cols-3">
                    {/* Main Form */}
                    <div className="lg:col-span-2 space-y-6">
                        {/* Payment Details */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <CreditCard className="h-5 w-5" />
                                    Payment Details
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid gap-4 md:grid-cols-2">
                                    <div className="space-y-2">
                                        <Label htmlFor="payment_date">Payment Date *</Label>
                                        <div className="relative">
                                            <Calendar className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                                            <Input
                                                id="payment_date"
                                                type="date"
                                                value={data.payment_date}
                                                onChange={(e) => setData('payment_date', e.target.value)}
                                                className="pl-10"
                                            />
                                        </div>
                                        {errors.payment_date && (
                                            <p className="text-sm text-red-600">{errors.payment_date}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="payment_method">Payment Method *</Label>
                                        <Select 
                                            value={data.payment_method} 
                                            onValueChange={(value) => setData('payment_method', value)}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select payment method" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="none">Select payment method</SelectItem>
                                                {PAYMENT_METHODS.map((method) => (
                                                    <SelectItem key={method.value} value={method.value}>
                                                        <div className="flex items-center gap-2">
                                                            <method.icon className="h-4 w-4" />
                                                            {method.label}
                                                        </div>
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {errors.payment_method && (
                                            <p className="text-sm text-red-600">{errors.payment_method}</p>
                                        )}
                                    </div>
                                </div>

                                <div className="grid gap-4 md:grid-cols-2">
                                    <div className="space-y-2">
                                        <Label htmlFor="amount">Amount *</Label>
                                        <div className="relative">
                                            <DollarSign className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                                            <Input
                                                id="amount"
                                                type="number"
                                                step="0.01"
                                                min="0.01"
                                                max={invoice.balance_due}
                                                value={data.amount}
                                                onChange={(e) => setData('amount', e.target.value)}
                                                className="pl-10"
                                                placeholder="0.00"
                                            />
                                        </div>
                                        {errors.amount && (
                                            <p className="text-sm text-red-600">{errors.amount}</p>
                                        )}
                                        <p className="text-sm text-muted-foreground">
                                            Maximum: {formatCurrency(invoice.balance_due)}
                                        </p>
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="fee_amount">Fee Amount</Label>
                                        <div className="relative">
                                            <DollarSign className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                                            <Input
                                                id="fee_amount"
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                value={data.fee_amount}
                                                onChange={(e) => setData('fee_amount', e.target.value)}
                                                className="pl-10"
                                                placeholder="0.00"
                                            />
                                        </div>
                                        {errors.fee_amount && (
                                            <p className="text-sm text-red-600">{errors.fee_amount}</p>
                                        )}
                                    </div>
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="reference">Reference/Check Number</Label>
                                    <Input
                                        id="reference"
                                        value={data.reference}
                                        onChange={(e) => setData('reference', e.target.value)}
                                        placeholder="Enter reference number"
                                    />
                                    {errors.reference && (
                                        <p className="text-sm text-red-600">{errors.reference}</p>
                                    )}
                                </div>
                            </CardContent>
                        </Card>

                        {/* Additional Information */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Additional Information</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid gap-4 md:grid-cols-2">
                                    <div className="space-y-2">
                                        <Label htmlFor="bank_name">Bank Name</Label>
                                        <Input
                                            id="bank_name"
                                            value={data.bank_name}
                                            onChange={(e) => setData('bank_name', e.target.value)}
                                            placeholder="Enter bank name"
                                        />
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="check_number">Check Number</Label>
                                        <Input
                                            id="check_number"
                                            value={data.check_number}
                                            onChange={(e) => setData('check_number', e.target.value)}
                                            placeholder="Enter check number"
                                        />
                                    </div>
                                </div>

                                <div className="grid gap-4 md:grid-cols-2">
                                    <div className="space-y-2">
                                        <Label htmlFor="transaction_id">Transaction ID</Label>
                                        <Input
                                            id="transaction_id"
                                            value={data.transaction_id}
                                            onChange={(e) => setData('transaction_id', e.target.value)}
                                            placeholder="Enter transaction ID"
                                        />
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="received_by">Received By</Label>
                                        <Input
                                            id="received_by"
                                            value={data.received_by}
                                            onChange={(e) => setData('received_by', e.target.value)}
                                            placeholder="Enter recipient name"
                                        />
                                    </div>
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="notes">Notes</Label>
                                    <Textarea
                                        id="notes"
                                        value={data.notes}
                                        onChange={(e) => setData('notes', e.target.value)}
                                        placeholder="Enter payment notes"
                                        rows={3}
                                    />
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Sidebar */}
                    <div className="space-y-4">
                        {/* Invoice Information */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <FileText className="h-5 w-5" />
                                    Invoice Details
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3">
                                <div className="flex justify-between">
                                    <span>Invoice #:</span>
                                    <span className="font-medium">{invoice.invoice_no}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span>Date:</span>
                                    <span className="font-medium">{formatDate(invoice.date)}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span>Total Amount:</span>
                                    <span className="font-medium">{formatCurrency(invoice.total_amount)}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span>Balance Due:</span>
                                    <span className="font-medium text-red-600">{formatCurrency(invoice.balance_due)}</span>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Customer Information */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <User className="h-5 w-5" />
                                    Customer
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-2">
                                <div className="font-medium">{invoice.customer.name}</div>
                                <div className="text-sm text-muted-foreground">{invoice.customer.email}</div>
                                <div className="text-sm text-muted-foreground">{invoice.customer.phone}</div>
                            </CardContent>
                        </Card>

                        {/* Payment Summary */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Payment Summary</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3">
                                <div className="flex justify-between">
                                    <span>Payment Amount:</span>
                                    <span className="font-medium">{formatCurrency(parseFloat(data.amount) || 0)}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span>Fee Amount:</span>
                                    <span className="font-medium">{formatCurrency(parseFloat(data.fee_amount) || 0)}</span>
                                </div>
                                <div className="border-t pt-3">
                                    <div className="flex justify-between text-lg font-bold">
                                        <span>Net Amount:</span>
                                        <span>{formatCurrency(netAmount)}</span>
                                    </div>
                                </div>
                                <div className="border-t pt-3">
                                    <div className="flex justify-between text-lg font-bold">
                                        <span>Remaining Balance:</span>
                                        <span className="text-red-600">
                                            {formatCurrency(invoice.balance_due - (parseFloat(data.amount) || 0))}
                                        </span>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Action Buttons */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Actions</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-2">
                                <Button type="submit" className="w-full" disabled={processing} onClick={handleSubmit}>
                                    <Save className="h-4 w-4 mr-2" />
                                    {processing ? 'Processing...' : 'Receive Payment'}
                                </Button>
                                
                                <Link href={`/invoices/${invoice.id}`}>
                                    <Button variant="outline" className="w-full">
                                        <X className="h-4 w-4 mr-2" />
                                        Cancel
                                    </Button>
                                </Link>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

