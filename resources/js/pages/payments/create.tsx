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
    FileText
} from 'lucide-react';

interface Invoice {
    id: number;
    invoice_no: string;
    total_amount: number;
    balance_due: number;
    customer: {
        id: number;
        name: string;
        email: string;
    };
}

interface CreatePaymentProps {
    invoice?: Invoice;
    invoices: Invoice[];
}

const PAYMENT_METHODS = [
    { value: 'cash', label: 'Cash', icon: DollarSign },
    { value: 'check', label: 'Check', icon: Receipt },
    { value: 'credit card', label: 'Credit Card', icon: CreditCard },
    { value: 'bank transfer', label: 'Bank Transfer', icon: Building },
    { value: 'online', label: 'Online Payment', icon: CreditCard },
];

const PAYMENT_STATUSES = [
    { value: 'completed', label: 'Completed', color: 'green' },
    { value: 'pending', label: 'Pending', color: 'yellow' },
    { value: 'failed', label: 'Failed', color: 'red' },
    { value: 'cancelled', label: 'Cancelled', color: 'gray' },
];

export default function CreatePayment({ invoice, invoices }: CreatePaymentProps) {
    const { data, setData, post, processing, errors } = useForm({
        invoice_id: invoice?.id?.toString() || 'none',
        payment_date: new Date().toISOString().split('T')[0],
        payment_method: 'none',
        amount: invoice?.balance_due?.toString() || '0',
        reference: '',
        notes: '',
        status: 'completed',
        bank_name: '',
        check_number: '',
        transaction_id: '',
        fee_amount: '0',
        received_by: '',
    });

    const [selectedInvoice, setSelectedInvoice] = useState<Invoice | null>(invoice || null);
    const [netAmount, setNetAmount] = useState(0);

    // Calculate net amount when amount or fee changes
    useEffect(() => {
        const amount = parseFloat(data.amount) || 0;
        const fee = parseFloat(data.fee_amount) || 0;
        setNetAmount(amount - fee);
    }, [data.amount, data.fee_amount]);

    // Update amount when invoice changes
    useEffect(() => {
        if (selectedInvoice) {
            setData('amount', selectedInvoice.balance_due.toString());
        }
    }, [selectedInvoice]);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        
        // Process form data to convert "none" values to empty strings
        const processedData = {
            ...data,
            invoice_id: data.invoice_id === 'none' ? '' : data.invoice_id,
            payment_method: data.payment_method === 'none' ? '' : data.payment_method,
        };
        
        // Update form data with processed values
        Object.keys(processedData).forEach(key => {
            setData(key as keyof typeof data, processedData[key as keyof typeof processedData]);
        });
        
        post('/payments');
    };

    const formatCurrency = (amount: number) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(amount);
    };

    const getStatusColor = (status: string) => {
        const statusConfig = PAYMENT_STATUSES.find(s => s.value === status);
        return statusConfig ? `bg-${statusConfig.color}-50 text-${statusConfig.color}-700 border-${statusConfig.color}-200` : 'bg-gray-50 text-gray-700 border-gray-200';
    };

    return (
        <AuthenticatedLayout>
            <Head title="Record Payment" />
            
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Link href="/payments">
                            <Button variant="outline" size="sm">
                                <ArrowLeft className="h-4 w-4 mr-2" />
                                Back to Payments
                            </Button>
                        </Link>
                        <div>
                            <h1 className="text-3xl font-bold tracking-tight">Record Payment</h1>
                            <p className="text-muted-foreground">
                                Record a new payment transaction
                            </p>
                        </div>
                    </div>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
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
                                            <Label htmlFor="invoice_id">Invoice *</Label>
                                            <Select 
                                                value={data.invoice_id} 
                                                onValueChange={(value) => {
                                                    if (value === 'none') {
                                                        setData('invoice_id', '');
                                                        setSelectedInvoice(null);
                                                    } else {
                                                        setData('invoice_id', value);
                                                        const invoice = invoices.find(i => i.id.toString() === value);
                                                        setSelectedInvoice(invoice || null);
                                                    }
                                                }}
                                            >
                                                <SelectTrigger>
                                                    <SelectValue placeholder="Select invoice" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="none">Select an invoice</SelectItem>
                                                    {invoices.map((invoice) => (
                                                        <SelectItem key={invoice.id} value={invoice.id.toString()}>
                                                            {invoice.invoice_no} - {invoice.customer.name} ({formatCurrency(invoice.balance_due)})
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                            {errors.invoice_id && (
                                                <p className="text-sm text-red-600">{errors.invoice_id}</p>
                                            )}
                                        </div>

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
                                    </div>

                                    <div className="grid gap-4 md:grid-cols-2">
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

                                        <div className="space-y-2">
                                            <Label htmlFor="status">Status *</Label>
                                            <Select 
                                                value={data.status} 
                                                onValueChange={(value) => setData('status', value)}
                                            >
                                                <SelectTrigger>
                                                    <SelectValue placeholder="Select status" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {PAYMENT_STATUSES.map((status) => (
                                                        <SelectItem key={status.value} value={status.value}>
                                                            <div className="flex items-center gap-2">
                                                                <Badge className={getStatusColor(status.value)}>
                                                                    {status.label}
                                                                </Badge>
                                                            </div>
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                            {errors.status && (
                                                <p className="text-sm text-red-600">{errors.status}</p>
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
                                                    value={data.amount}
                                                    onChange={(e) => setData('amount', e.target.value)}
                                                    className="pl-10"
                                                    placeholder="0.00"
                                                />
                                            </div>
                                            {errors.amount && (
                                                <p className="text-sm text-red-600">{errors.amount}</p>
                                            )}
                                            {selectedInvoice && (
                                                <p className="text-sm text-muted-foreground">
                                                    Invoice balance: {formatCurrency(selectedInvoice.balance_due)}
                                                </p>
                                            )}
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
                                </CardContent>
                            </Card>

                            {/* Invoice Information */}
                            {selectedInvoice && (
                                <Card>
                                    <CardHeader>
                                        <CardTitle className="flex items-center gap-2">
                                            <FileText className="h-5 w-5" />
                                            Invoice Details
                                        </CardTitle>
                                    </CardHeader>
                                    <CardContent className="space-y-2">
                                        <div className="flex justify-between">
                                            <span>Invoice #:</span>
                                            <span className="font-medium">{selectedInvoice.invoice_no}</span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span>Customer:</span>
                                            <span className="font-medium">{selectedInvoice.customer.name}</span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span>Total Amount:</span>
                                            <span className="font-medium">{formatCurrency(selectedInvoice.total_amount)}</span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span>Balance Due:</span>
                                            <span className="font-medium text-red-600">{formatCurrency(selectedInvoice.balance_due)}</span>
                                        </div>
                                    </CardContent>
                                </Card>
                            )}

                            {/* Action Buttons */}
                            <Card>
                                <CardHeader>
                                    <CardTitle>Actions</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-2">
                                    <Button type="submit" className="w-full" disabled={processing}>
                                        <Save className="h-4 w-4 mr-2" />
                                        {processing ? 'Recording...' : 'Record Payment'}
                                    </Button>
                                    
                                    <Link href="/payments">
                                        <Button variant="outline" className="w-full">
                                            <X className="h-4 w-4 mr-2" />
                                            Cancel
                                        </Button>
                                    </Link>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}

