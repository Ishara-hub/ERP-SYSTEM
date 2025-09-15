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
    FileText, 
    Mail, 
    Printer,
    DollarSign,
    Calendar,
    ArrowUpDown,
    ChevronUp,
    ChevronDown,
    Filter
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

interface Invoice {
    id: number;
    invoice_no: string;
    date: string;
    total_amount: number;
    status: string;
    balance_due: number;
    created_at: string;
    customer: {
        id: number;
        name: string;
        email: string;
    };
    line_items_count?: number;
}

interface InvoicesIndexProps {
    invoices: {
        data: Invoice[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: any[];
    };
    filters: {
        search?: string;
        status?: string;
        date_from?: string;
        date_to?: string;
        sort_by?: string;
        sort_direction?: string;
    };
}

export default function InvoicesIndex({ invoices, filters }: InvoicesIndexProps) {
    const [search, setSearch] = useState(filters.search || '');
    const [status, setStatus] = useState(filters.status || '');
    const [dateFrom, setDateFrom] = useState(filters.date_from || '');
    const [dateTo, setDateTo] = useState(filters.date_to || '');
    const [sortBy, setSortBy] = useState(filters.sort_by || 'created_at');
    const [sortDirection, setSortDirection] = useState(filters.sort_direction || 'desc');
    const [deleteInvoice, setDeleteInvoice] = useState<Invoice | null>(null);

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get('/invoices', { 
            search, 
            status: status === 'all' ? '' : status, 
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
        router.get('/invoices', { 
            search, 
            status: status === 'all' ? '' : status, 
            date_from: dateFrom, 
            date_to: dateTo,
            sort_by: field, 
            sort_direction: newDirection 
        }, { preserveState: true });
    };

    const handleDelete = (invoice: Invoice) => {
        setDeleteInvoice(invoice);
    };

    const confirmDelete = () => {
        if (deleteInvoice) {
            router.delete(`/invoices/${deleteInvoice.id}`, {
                onSuccess: () => setDeleteInvoice(null)
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

    const totalAmount = invoices.data.reduce((sum, invoice) => sum + invoice.total_amount, 0);
    const totalBalance = invoices.data.reduce((sum, invoice) => sum + invoice.balance_due, 0);

    return (
        <AuthenticatedLayout>
            <Head title="Invoices" />
            
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Invoices</h1>
                        <p className="text-muted-foreground">
                            Manage your customer invoices
                        </p>
                    </div>
                    <Link href="/invoices/create">
                        <Button>
                            <Plus className="h-4 w-4 mr-2" />
                            Create Invoice
                        </Button>
                    </Link>
                </div>

                {/* Summary Cards */}
                <div className="grid gap-4 md:grid-cols-3">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Invoices</CardTitle>
                            <FileText className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{invoices.total}</div>
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
                            <CardTitle className="text-sm font-medium">Outstanding Balance</CardTitle>
                            <DollarSign className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{formatCurrency(totalBalance)}</div>
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
                            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                                <div className="relative">
                                    <Search className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                                    <Input
                                        placeholder="Search invoices..."
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
                                        <SelectItem value="unpaid">Unpaid</SelectItem>
                                        <SelectItem value="partial">Partial</SelectItem>
                                        <SelectItem value="paid">Paid</SelectItem>
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

                {/* Invoices Table */}
                <Card>
                    <CardHeader>
                        <CardTitle>Invoice List</CardTitle>
                        <CardDescription>
                            {invoices.total} invoice{invoices.total !== 1 ? 's' : ''} found
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
                                                onClick={() => handleSort('invoice_no')}
                                                className="h-auto p-0 font-semibold"
                                            >
                                                Invoice # {getSortIcon('invoice_no')}
                                            </Button>
                                        </th>
                                        <th className="text-left p-4">
                                            <Button
                                                variant="ghost"
                                                onClick={() => handleSort('date')}
                                                className="h-auto p-0 font-semibold"
                                            >
                                                Date {getSortIcon('date')}
                                            </Button>
                                        </th>
                                        <th className="text-left p-4">Customer</th>
                                        <th className="text-left p-4">
                                            <Button
                                                variant="ghost"
                                                onClick={() => handleSort('total_amount')}
                                                className="h-auto p-0 font-semibold"
                                            >
                                                Amount {getSortIcon('total_amount')}
                                            </Button>
                                        </th>
                                        <th className="text-left p-4">Status</th>
                                        <th className="text-left p-4">Balance</th>
                                        <th className="text-left p-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {invoices.data.map((invoice) => (
                                        <tr key={invoice.id} className="border-b hover:bg-muted/50">
                                            <td className="p-4">
                                                <div className="font-medium">{invoice.invoice_no}</div>
                                            </td>
                                            <td className="p-4">
                                                <div className="flex items-center gap-2">
                                                    <Calendar className="h-4 w-4 text-muted-foreground" />
                                                    {formatDate(invoice.date)}
                                                </div>
                                            </td>
                                            <td className="p-4">
                                                <div>
                                                    <div className="font-medium">{invoice.customer.name}</div>
                                                    <div className="text-sm text-muted-foreground">{invoice.customer.email}</div>
                                                </div>
                                            </td>
                                            <td className="p-4">
                                                <div className="font-medium">{formatCurrency(invoice.total_amount)}</div>
                                            </td>
                                            <td className="p-4">
                                                <Badge className={getStatusColor(invoice.status)}>
                                                    {invoice.status.toUpperCase()}
                                                </Badge>
                                            </td>
                                            <td className="p-4">
                                                <div className="font-medium">{formatCurrency(invoice.balance_due)}</div>
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
                                                            <Link href={`/invoices/${invoice.id}`}>
                                                                <Eye className="h-4 w-4 mr-2" />
                                                                View
                                                            </Link>
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem asChild>
                                                            <Link href={`/invoices/${invoice.id}/edit`}>
                                                                <Edit className="h-4 w-4 mr-2" />
                                                                Edit
                                                            </Link>
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem asChild>
                                                            <Link href={`/invoices/${invoice.id}/print`}>
                                                                <Printer className="h-4 w-4 mr-2" />
                                                                Print
                                                            </Link>
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem asChild>
                                                            <Link href={`/invoices/${invoice.id}/email`}>
                                                                <Mail className="h-4 w-4 mr-2" />
                                                                Email
                                                            </Link>
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem 
                                                            onClick={() => handleDelete(invoice)}
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
                        {invoices.last_page > 1 && (
                            <div className="flex items-center justify-between mt-6">
                                <div className="text-sm text-muted-foreground">
                                    Showing {((invoices.current_page - 1) * invoices.per_page) + 1} to{' '}
                                    {Math.min(invoices.current_page * invoices.per_page, invoices.total)} of{' '}
                                    {invoices.total} results
                                </div>
                                <div className="flex gap-2">
                                    {invoices.links.map((link: any, index: number) => (
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
            <Dialog open={!!deleteInvoice} onOpenChange={() => setDeleteInvoice(null)}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Delete Invoice</DialogTitle>
                        <DialogDescription>
                            Are you sure you want to delete invoice "{deleteInvoice?.invoice_no}"? This action cannot be undone.
                            {deleteInvoice && (deleteInvoice.line_items_count || 0) > 0 && (
                                <div className="mt-2 p-2 bg-yellow-50 border border-yellow-200 rounded text-yellow-800">
                                    <strong>Warning:</strong> This invoice has {(deleteInvoice.line_items_count || 0)} line item(s). 
                                    You may not be able to delete this invoice.
                                </div>
                            )}
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setDeleteInvoice(null)}>
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
