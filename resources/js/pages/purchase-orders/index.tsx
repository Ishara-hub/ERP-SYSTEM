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
    Building,
    Calendar,
    DollarSign,
    ArrowUpDown,
    ChevronUp,
    ChevronDown,
    Filter,
    Package,
    Printer
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

interface PurchaseOrder {
    id: number;
    po_number: string;
    order_date: string;
    expected_delivery_date: string;
    status: string;
    total_amount: number;
    supplier: {
        id: number;
        name: string;
        company_name: string;
    };
    items: any[];
}

interface PurchaseOrdersIndexProps {
    purchaseOrders: {
        data: PurchaseOrder[];
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

export default function PurchaseOrdersIndex({ purchaseOrders, filters }: PurchaseOrdersIndexProps) {
    const [search, setSearch] = useState(filters.search || '');
    const [status, setStatus] = useState(filters.status || 'all');
    const [dateFrom, setDateFrom] = useState(filters.date_from || '');
    const [dateTo, setDateTo] = useState(filters.date_to || '');
    const [sortBy, setSortBy] = useState(filters.sort_by || 'created_at');
    const [sortDirection, setSortDirection] = useState(filters.sort_direction || 'desc');
    const [deletePurchaseOrder, setDeletePurchaseOrder] = useState<PurchaseOrder | null>(null);

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get('/purchase-orders', { 
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
        router.get('/purchase-orders', { 
            search, 
            status: status === 'all' ? '' : status,
            date_from: dateFrom,
            date_to: dateTo,
            sort_by: field, 
            sort_direction: newDirection 
        }, { preserveState: true });
    };

    const handleDelete = (purchaseOrder: PurchaseOrder) => {
        setDeletePurchaseOrder(purchaseOrder);
    };

    const confirmDelete = () => {
        if (deletePurchaseOrder) {
            router.delete(`/purchase-orders/${deletePurchaseOrder.id}`, {
                onSuccess: () => setDeletePurchaseOrder(null)
            });
        }
    };

    const handleStatusUpdate = (purchaseOrder: PurchaseOrder, newStatus: string) => {
        router.patch(`/purchase-orders/${purchaseOrder.id}/update-status`, {
            status: newStatus
        });
    };

    const getSortIcon = (field: string) => {
        if (sortBy !== field) return <ArrowUpDown className="h-4 w-4" />;
        return sortDirection === 'asc' ? <ChevronUp className="h-4 w-4" /> : <ChevronDown className="h-4 w-4" />;
    };

    const formatCurrency = (amount: number) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
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

    const statusOptions = [
        { value: 'draft', label: 'Draft' },
        { value: 'sent', label: 'Sent' },
        { value: 'confirmed', label: 'Confirmed' },
        { value: 'partial', label: 'Partial' },
        { value: 'received', label: 'Received' },
        { value: 'cancelled', label: 'Cancelled' },
    ];

    const totalValue = purchaseOrders.data.reduce((sum, po) => sum + po.total_amount, 0);
    const draftCount = purchaseOrders.data.filter(po => po.status === 'draft').length;
    const sentCount = purchaseOrders.data.filter(po => po.status === 'sent').length;
    const receivedCount = purchaseOrders.data.filter(po => po.status === 'received').length;

    return (
        <AuthenticatedLayout>
            <Head title="Purchase Orders" />
            
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Purchase Orders</h1>
                        <p className="text-muted-foreground">
                            Manage your purchase orders and procurement
                        </p>
                    </div>
                    <Link href="/purchase-orders/create">
                        <Button>
                            <Plus className="h-4 w-4 mr-2" />
                            Create Purchase Order
                        </Button>
                    </Link>
                </div>

                {/* Summary Cards */}
                <div className="grid gap-4 md:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Orders</CardTitle>
                            <FileText className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{purchaseOrders.total}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Value</CardTitle>
                            <DollarSign className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{formatCurrency(totalValue)}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Draft Orders</CardTitle>
                            <Package className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{draftCount}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Received</CardTitle>
                            <Package className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{receivedCount}</div>
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
                            <div className="grid gap-4 md:grid-cols-5">
                                <div className="relative">
                                    <Search className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                                    <Input
                                        placeholder="Search purchase orders..."
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
                                        {statusOptions.map((option) => (
                                            <SelectItem key={option.value} value={option.value}>
                                                {option.label}
                                            </SelectItem>
                                        ))}
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

                                <Button type="submit">Search</Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                {/* Purchase Orders Table */}
                <Card>
                    <CardHeader>
                        <CardTitle>Purchase Orders</CardTitle>
                        <CardDescription>
                            {purchaseOrders.total} purchase order{purchaseOrders.total !== 1 ? 's' : ''} found
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
                                                onClick={() => handleSort('po_number')}
                                                className="h-auto p-0 font-semibold"
                                            >
                                                PO Number {getSortIcon('po_number')}
                                            </Button>
                                        </th>
                                        <th className="text-left p-4">Supplier</th>
                                        <th className="text-left p-4">
                                            <Button
                                                variant="ghost"
                                                onClick={() => handleSort('order_date')}
                                                className="h-auto p-0 font-semibold"
                                            >
                                                Order Date {getSortIcon('order_date')}
                                            </Button>
                                        </th>
                                        <th className="text-left p-4">Expected Delivery</th>
                                        <th className="text-left p-4">
                                            <Button
                                                variant="ghost"
                                                onClick={() => handleSort('total_amount')}
                                                className="h-auto p-0 font-semibold"
                                            >
                                                Total Amount {getSortIcon('total_amount')}
                                            </Button>
                                        </th>
                                        <th className="text-left p-4">Status</th>
                                        <th className="text-left p-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {purchaseOrders.data.map((purchaseOrder) => (
                                        <tr key={purchaseOrder.id} className="border-b hover:bg-muted/50">
                                            <td className="p-4">
                                                <div className="font-medium font-mono">{purchaseOrder.po_number}</div>
                                            </td>
                                            <td className="p-4">
                                                <div>
                                                    <div className="font-medium">{purchaseOrder.supplier.name}</div>
                                                    {purchaseOrder.supplier.company_name && (
                                                        <div className="text-sm text-muted-foreground">{purchaseOrder.supplier.company_name}</div>
                                                    )}
                                                </div>
                                            </td>
                                            <td className="p-4">
                                                <div className="flex items-center gap-2 text-sm">
                                                    <Calendar className="h-4 w-4 text-muted-foreground" />
                                                    {formatDate(purchaseOrder.order_date)}
                                                </div>
                                            </td>
                                            <td className="p-4">
                                                <div className="text-sm">
                                                    {purchaseOrder.expected_delivery_date ? formatDate(purchaseOrder.expected_delivery_date) : 'Not set'}
                                                </div>
                                            </td>
                                            <td className="p-4">
                                                <div className="font-medium">{formatCurrency(purchaseOrder.total_amount)}</div>
                                            </td>
                                            <td className="p-4">
                                                <Badge className={getStatusColor(purchaseOrder.status)}>
                                                    {getStatusLabel(purchaseOrder.status)}
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
                                                            <Link href={`/purchase-orders/${purchaseOrder.id}`}>
                                                                <Eye className="h-4 w-4 mr-2" />
                                                                View
                                                            </Link>
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem asChild>
                                                            <Link href={`/purchase-orders/${purchaseOrder.id}/edit`}>
                                                                <Edit className="h-4 w-4 mr-2" />
                                                                Edit
                                                            </Link>
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem asChild>
                                                            <Link href={`/purchase-orders/${purchaseOrder.id}/print`}>
                                                                <Printer className="h-4 w-4 mr-2" />
                                                                Print
                                                            </Link>
                                                        </DropdownMenuItem>
                                                        {purchaseOrder.status === 'draft' && (
                                                            <DropdownMenuItem onClick={() => handleDelete(purchaseOrder)}>
                                                                <Trash2 className="h-4 w-4 mr-2" />
                                                                Delete
                                                            </DropdownMenuItem>
                                                        )}
                                                        {purchaseOrder.status !== 'received' && purchaseOrder.status !== 'cancelled' && (
                                                            <>
                                                                <DropdownMenuItem onClick={() => handleStatusUpdate(purchaseOrder, 'sent')}>
                                                                    Mark as Sent
                                                                </DropdownMenuItem>
                                                                <DropdownMenuItem onClick={() => handleStatusUpdate(purchaseOrder, 'confirmed')}>
                                                                    Mark as Confirmed
                                                                </DropdownMenuItem>
                                                                <DropdownMenuItem onClick={() => handleStatusUpdate(purchaseOrder, 'received')}>
                                                                    Mark as Received
                                                                </DropdownMenuItem>
                                                            </>
                                                        )}
                                                    </DropdownMenuContent>
                                                </DropdownMenu>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        {/* Pagination */}
                        {purchaseOrders.last_page > 1 && (
                            <div className="flex items-center justify-between mt-6">
                                <div className="text-sm text-muted-foreground">
                                    Showing {((purchaseOrders.current_page - 1) * purchaseOrders.per_page) + 1} to{' '}
                                    {Math.min(purchaseOrders.current_page * purchaseOrders.per_page, purchaseOrders.total)} of{' '}
                                    {purchaseOrders.total} results
                                </div>
                                <div className="flex gap-2">
                                    {purchaseOrders.links.map((link: any, index: number) => (
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
            <Dialog open={!!deletePurchaseOrder} onOpenChange={() => setDeletePurchaseOrder(null)}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Delete Purchase Order</DialogTitle>
                        <DialogDescription>
                            Are you sure you want to delete purchase order "{deletePurchaseOrder?.po_number}"? 
                            This action cannot be undone and will also delete all associated line items.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setDeletePurchaseOrder(null)}>
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





