import { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { 
    Search, 
    Plus, 
    Edit, 
    Eye, 
    Trash2, 
    CreditCard, 
    DollarSign,
    Calendar,
    ArrowUpDown,
    ChevronUp,
    ChevronDown,
    Filter,
    Receipt
} from 'lucide-react';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

interface Payment {
    id: number;
    payment_number: string;
    payment_date: string;
    payment_method: string;
    amount: number;
    fee_amount: number;
    net_amount: number;
    status: string;
    reference: string;
    notes: string;
    bank_name: string;
    check_number: string;
    transaction_id: string;
    received_by: string;
    created_at: string;
    invoice: {
        id: number;
        invoice_no: string;
        total_amount: number;
        balance_due: number;
        customer: {
            id: number;
            name: string;
            email: string;
        };
    };
}

interface PaymentsIndexProps {
    payments: {
        data: Payment[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: any[];
    };
    filters: {
        search?: string;
        status?: string;
        payment_method?: string;
        date_from?: string;
        date_to?: string;
        sort_by?: string;
        sort_direction?: string;
    };
}

export default function PaymentsIndex({ payments, filters }: PaymentsIndexProps) {
    const [search, setSearch] = useState(filters.search || '');
    const [status, setStatus] = useState(filters.status || 'all');
    const [paymentMethod, setPaymentMethod] = useState(filters.payment_method || 'all');
    const [dateFrom, setDateFrom] = useState(filters.date_from || '');
    const [dateTo, setDateTo] = useState(filters.date_to || '');
    const [sortBy, setSortBy] = useState(filters.sort_by || 'created_at');
    const [sortDirection, setSortDirection] = useState(filters.sort_direction || 'desc');
    const [deletePayment, setDeletePayment] = useState<Payment | null>(null);

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get('/payments', { 
            search, 
            status: status === 'all' ? '' : status, 
            payment_method: paymentMethod === 'all' ? '' : paymentMethod,
            date_from: dateFrom, 
            date_to: dateTo,
            sort_by: sortBy,
            sort_direction: sortDirection
        }, { preserveState: true });
    };

    const handleSort = (field: string) => {
        const newDirection = sortBy === field && sortDirection === 'asc' ? 'desc' : 'asc';
        setSortBy(field);
        setSortDirection(newDirection);
        router.get('/payments', { 
            search, 
            status: status === 'all' ? '' : status, 
            payment_method: paymentMethod === 'all' ? '' : paymentMethod,
            date_from: dateFrom, 
            date_to: dateTo,
            sort_by: field, 
            sort_direction: newDirection 
        }, { preserveState: true });
    };

    const handleDelete = (payment: Payment) => {
        setDeletePayment(payment);
    };

    const confirmDelete = () => {
        if (deletePayment) {
            router.delete(`/payments/${deletePayment.id}`, {
                onSuccess: () => setDeletePayment(null)
            });
        }
    };

    const getSortIcon = (field: string) => {
        if (sortBy !== field) return <ArrowUpDown className="h-4 w-4" />;
        return sortDirection === 'asc' ? <ChevronUp className="h-4 w-4" /> : <ChevronDown className="h-4 w-4" />;
    };

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
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
            case 'completed':
                return 'bg-green-50 text-green-700 border-green-200';
            case 'pending':
                return 'bg-yellow-50 text-yellow-700 border-yellow-200';
            case 'failed':
                return 'bg-red-50 text-red-700 border-red-200';
            case 'cancelled':
                return 'bg-gray-50 text-gray-700 border-gray-200';
            default:
                return 'bg-gray-50 text-gray-700 border-gray-200';
        }
    };

    const getPaymentMethodIcon = (method: string) => {
        switch (method.toLowerCase()) {
            case 'cash':
                return <DollarSign className="h-4 w-4" />;
            case 'check':
                return <Receipt className="h-4 w-4" />;
            case 'credit card':
            case 'card':
                return <CreditCard className="h-4 w-4" />;
            default:
                return <CreditCard className="h-4 w-4" />;
        }
    };

    const totalAmount = payments.data.reduce((sum, payment) => sum + payment.amount, 0);
    const totalFees = payments.data.reduce((sum, payment) => sum + payment.fee_amount, 0);
    const netAmount = totalAmount - totalFees;

    return (
        <AuthenticatedLayout>
            <Head title="Payments" />
            
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Payments</h1>
                        <p className="text-muted-foreground">
                            Manage customer payments and transactions
                        </p>
                    </div>
                    <div className="flex gap-2">
                        <Link href="/payments/create">
                            <Button variant="outline">
                                <Plus className="h-4 w-4 mr-2" />
                                Record Payment
                            </Button>
                        </Link>
                        <Link href="/payments/general">
                            <Button>
                                <Receipt className="h-4 w-4 mr-2" />
                                General Payment
                            </Button>
                        </Link>
                    </div>
                </div>

                {/* Summary Cards */}
                <div className="grid gap-4 md:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Payments</CardTitle>
                            <CreditCard className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{payments.total}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Amount</CardTitle>
                            <DollarSign className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{formatCurrency(totalAmount)}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Fees</CardTitle>
                            <DollarSign className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{formatCurrency(totalFees)}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Net Amount</CardTitle>
                            <DollarSign className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{formatCurrency(netAmount)}</div>
                        </CardContent>
                    </Card>
                </div>

                {/* Search and Filters */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Filter className="h-5 w-5" />
                            Search & Filter
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSearch} className="space-y-4">
                            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
                                <div className="relative">
                                    <Search className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                                    <Input
                                        placeholder="Search payments..."
                                        value={search}
                                        onChange={(e) => setSearch(e.target.value)}
                                        className="pl-10"
                                    />
                                </div>
                                
                                <Select value={status} onValueChange={setStatus}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="All Statuses" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Statuses</SelectItem>
                                        <SelectItem value="completed">Completed</SelectItem>
                                        <SelectItem value="pending">Pending</SelectItem>
                                        <SelectItem value="failed">Failed</SelectItem>
                                        <SelectItem value="cancelled">Cancelled</SelectItem>
                                    </SelectContent>
                                </Select>

                                <Select value={paymentMethod} onValueChange={setPaymentMethod}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="All Methods" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Methods</SelectItem>
                                        <SelectItem value="cash">Cash</SelectItem>
                                        <SelectItem value="check">Check</SelectItem>
                                        <SelectItem value="credit card">Credit Card</SelectItem>
                                        <SelectItem value="bank transfer">Bank Transfer</SelectItem>
                                        <SelectItem value="online">Online Payment</SelectItem>
                                    </SelectContent>
                                </Select>

                                <Input
                                    type="date"
                                    placeholder="From Date"
                                    value={dateFrom}
                                    onChange={(e) => setDateFrom(e.target.value)}
                                />

                                <Input
                                    type="date"
                                    placeholder="To Date"
                                    value={dateTo}
                                    onChange={(e) => setDateTo(e.target.value)}
                                />
                            </div>
                            <Button type="submit">Search</Button>
                        </form>
                    </CardContent>
                </Card>

                {/* Payments Table */}
                <Card>
                    <CardHeader>
                        <CardTitle>Payment List</CardTitle>
                        <CardDescription>
                            {payments.total} payment{payments.total !== 1 ? 's' : ''} found
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead>
                                    <tr className="border-b">
                                        <th className="text-left p-4">
                                            <Button
                                                variant="ghost"
                                                onClick={() => handleSort('payment_number')}
                                                className="h-auto p-0 font-semibold"
                                            >
                                                Payment # {getSortIcon('payment_number')}
                                            </Button>
                                        </th>
                                        <th className="text-left p-4">
                                            <Button
                                                variant="ghost"
                                                onClick={() => handleSort('payment_date')}
                                                className="h-auto p-0 font-semibold"
                                            >
                                                Date {getSortIcon('payment_date')}
                                            </Button>
                                        </th>
                                        <th className="text-left p-4">Invoice</th>
                                        <th className="text-left p-4">Customer</th>
                                        <th className="text-left p-4">Method</th>
                                        <th className="text-left p-4">
                                            <Button
                                                variant="ghost"
                                                onClick={() => handleSort('amount')}
                                                className="h-auto p-0 font-semibold"
                                            >
                                                Amount {getSortIcon('amount')}
                                            </Button>
                                        </th>
                                        <th className="text-left p-4">Status</th>
                                        <th className="text-left p-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {payments.data.map((payment) => (
                                        <tr key={payment.id} className="border-b hover:bg-muted/50">
                                            <td className="p-4">
                                                <div className="font-medium">{payment.payment_number}</div>
                                            </td>
                                            <td className="p-4">
                                                <div className="flex items-center gap-2">
                                                    <Calendar className="h-4 w-4 text-muted-foreground" />
                                                    {formatDate(payment.payment_date)}
                                                </div>
                                            </td>
                                            <td className="p-4">
                                                <Link 
                                                    href={`/invoices/${payment.invoice.id}`}
                                                    className="text-blue-600 hover:text-blue-800 font-medium"
                                                >
                                                    {payment.invoice.invoice_no}
                                                </Link>
                                            </td>
                                            <td className="p-4">
                                                <div>
                                                    <div className="font-medium">{payment.invoice.customer.name}</div>
                                                    <div className="text-sm text-muted-foreground">{payment.invoice.customer.email}</div>
                                                </div>
                                            </td>
                                            <td className="p-4">
                                                <div className="flex items-center gap-2">
                                                    {getPaymentMethodIcon(payment.payment_method)}
                                                    <span className="capitalize">{payment.payment_method}</span>
                                                </div>
                                            </td>
                                            <td className="p-4">
                                                <div className="font-medium">{formatCurrency(payment.amount)}</div>
                                                {payment.fee_amount > 0 && (
                                                    <div className="text-sm text-muted-foreground">
                                                        Fee: {formatCurrency(payment.fee_amount)}
                                                    </div>
                                                )}
                                            </td>
                                            <td className="p-4">
                                                <Badge className={getStatusColor(payment.status)}>
                                                    {payment.status.toUpperCase()}
                                                </Badge>
                                            </td>
                                            <td className="p-4">
                                                <DropdownMenu>
                                                    <DropdownMenuTrigger asChild>
                                                        <Button variant="ghost" size="sm">
                                                            Actions
                                                        </Button>
                                                    </DropdownMenuTrigger>
                                                    <DropdownMenuContent align="end">
                                                        <DropdownMenuItem asChild>
                                                            <Link href={`/payments/${payment.id}`}>
                                                                <Eye className="h-4 w-4 mr-2" />
                                                                View
                                                            </Link>
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem asChild>
                                                            <Link href={`/payments/${payment.id}/edit`}>
                                                                <Edit className="h-4 w-4 mr-2" />
                                                                Edit
                                                            </Link>
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem 
                                                            onClick={() => handleDelete(payment)}
                                                            className="text-red-600"
                                                        >
                                                            <Trash2 className="h-4 w-4 mr-2" />
                                                            Delete
                                                        </DropdownMenuItem>
                                                    </DropdownMenuContent>
                                                </DropdownMenu>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        {/* Pagination */}
                        {payments.last_page > 1 && (
                            <div className="flex items-center justify-between mt-6">
                                <div className="text-sm text-muted-foreground">
                                    Showing {((payments.current_page - 1) * payments.per_page) + 1} to{' '}
                                    {Math.min(payments.current_page * payments.per_page, payments.total)} of{' '}
                                    {payments.total} results
                                </div>
                                <div className="flex gap-2">
                                    {payments.links.map((link: any, index: number) => (
                                        <Button
                                            key={index}
                                            variant={link.active ? "default" : "outline"}
                                            size="sm"
                                            onClick={() => link.url && router.get(link.url)}
                                            disabled={!link.url}
                                        >
                                            <span dangerouslySetInnerHTML={{ __html: link.label }} />
                                        </Button>
                                    ))}
                                </div>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>

            {/* Delete Confirmation Dialog */}
            <Dialog open={!!deletePayment} onOpenChange={() => setDeletePayment(null)}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Delete Payment</DialogTitle>
                        <DialogDescription>
                            Are you sure you want to delete payment "{deletePayment?.payment_number}"? This action cannot be undone.
                            This will also update the associated invoice's payment status.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setDeletePayment(null)}>
                            Cancel
                        </Button>
                        <Button onClick={confirmDelete} className="bg-red-600 hover:bg-red-700">
                            Delete
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AuthenticatedLayout>
    );
}

