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
    Building, 
    Mail,
    Phone,
    Globe,
    ArrowUpDown,
    ChevronUp,
    ChevronDown,
    Filter,
    Users,
    DollarSign,
    Package
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
    purchase_orders_count: number;
}

interface SuppliersIndexProps {
    suppliers: {
        data: Supplier[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: any[];
    };
    filters: {
        search?: string;
        status?: string;
        sort_by?: string;
        sort_direction?: string;
    };
}

export default function SuppliersIndex({ suppliers, filters }: SuppliersIndexProps) {
    const [search, setSearch] = useState(filters.search || '');
    const [status, setStatus] = useState(filters.status || 'all');
    const [sortBy, setSortBy] = useState(filters.sort_by || 'created_at');
    const [sortDirection, setSortDirection] = useState(filters.sort_direction || 'desc');
    const [deleteSupplier, setDeleteSupplier] = useState<Supplier | null>(null);

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get('/suppliers', { 
            search, 
            status: status === 'all' ? '' : status, 
            sort_by: sortBy,
            sort_direction: sortDirection
        }, { preserveState: true });
    };

    const handleSort = (field: string) => {
        const newDirection = sortBy === field && sortDirection === 'asc' ? 'desc' : 'asc';
        setSortBy(field);
        setSortDirection(newDirection);
        router.get('/suppliers', { 
            search, 
            status: status === 'all' ? '' : status, 
            sort_by: field, 
            sort_direction: newDirection 
        }, { preserveState: true });
    };

    const handleDelete = (supplier: Supplier) => {
        setDeleteSupplier(supplier);
    };

    const confirmDelete = () => {
        if (deleteSupplier) {
            router.delete(`/suppliers/${deleteSupplier.id}`, {
                onSuccess: () => setDeleteSupplier(null)
            });
        }
    };

    const handleToggleStatus = (supplier: Supplier) => {
        router.patch(`/suppliers/${supplier.id}/toggle-status`, {}, {
            onSuccess: () => {
                // The page will refresh with updated data
            }
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

    const getStatusColor = (isActive: boolean) => {
        return isActive 
            ? 'bg-green-50 text-green-700 border-green-200' 
            : 'bg-red-50 text-red-700 border-red-200';
    };

    const activeSuppliers = suppliers.data.filter(s => s.is_active).length;
    const totalOrders = suppliers.data.reduce((sum, supplier) => sum + supplier.purchase_orders_count, 0);

    return (
        <AuthenticatedLayout>
            <Head title="Suppliers" />
            
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Suppliers</h1>
                        <p className="text-muted-foreground">
                            Manage your suppliers and vendors
                        </p>
                    </div>
                    <Link href="/suppliers/create">
                        <Button>
                            <Plus className="h-4 w-4 mr-2" />
                            Add Supplier
                        </Button>
                    </Link>
                </div>

                {/* Summary Cards */}
                <div className="grid gap-4 md:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Suppliers</CardTitle>
                            <Building className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{suppliers.total}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Active Suppliers</CardTitle>
                            <Users className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{activeSuppliers}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Orders</CardTitle>
                            <Package className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{totalOrders}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Avg Credit Limit</CardTitle>
                            <DollarSign className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {formatCurrency(
                                    suppliers.data.reduce((sum, s) => sum + (s.credit_limit || 0), 0) / 
                                    Math.max(suppliers.data.length, 1)
                                )}
                            </div>
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
                            <div className="grid gap-4 md:grid-cols-3">
                                <div className="relative">
                                    <Search className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                                    <Input
                                        placeholder="Search suppliers..."
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
                                        <SelectItem value="active">Active</SelectItem>
                                        <SelectItem value="inactive">Inactive</SelectItem>
                                    </SelectContent>
                                </Select>

                                <Button type="submit">Search</Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                {/* Suppliers Table */}
                <Card>
                    <CardHeader>
                        <CardTitle>Supplier List</CardTitle>
                        <CardDescription>
                            {suppliers.total} supplier{suppliers.total !== 1 ? 's' : ''} found
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
                                                onClick={() => handleSort('name')}
                                                className="h-auto p-0 font-semibold"
                                            >
                                                Supplier {getSortIcon('name')}
                                            </Button>
                                        </th>
                                        <th className="text-left p-4">Contact</th>
                                        <th className="text-left p-4">Code</th>
                                        <th className="text-left p-4">Orders</th>
                                        <th className="text-left p-4">Credit Limit</th>
                                        <th className="text-left p-4">Status</th>
                                        <th className="text-left p-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {suppliers.data.map((supplier) => (
                                        <tr key={supplier.id} className="border-b hover:bg-muted/50">
                                            <td className="p-4">
                                                <div>
                                                    <div className="font-medium">{supplier.display_name || supplier.name}</div>
                                                    {supplier.company_name && supplier.company_name !== supplier.name && (
                                                        <div className="text-sm text-muted-foreground">{supplier.company_name}</div>
                                                    )}
                                                </div>
                                            </td>
                                            <td className="p-4">
                                                <div className="space-y-1">
                                                    <div className="flex items-center gap-2 text-sm">
                                                        <Mail className="h-3 w-3 text-muted-foreground" />
                                                        {supplier.email}
                                                    </div>
                                                    <div className="flex items-center gap-2 text-sm">
                                                        <Phone className="h-3 w-3 text-muted-foreground" />
                                                        {supplier.phone}
                                                    </div>
                                                    {supplier.website && (
                                                        <div className="flex items-center gap-2 text-sm">
                                                            <Globe className="h-3 w-3 text-muted-foreground" />
                                                            <a href={supplier.website} target="_blank" rel="noopener noreferrer" className="text-blue-600 hover:underline">
                                                                Website
                                                            </a>
                                                        </div>
                                                    )}
                                                </div>
                                            </td>
                                            <td className="p-4">
                                                <div className="font-mono text-sm">{supplier.supplier_code}</div>
                                            </td>
                                            <td className="p-4">
                                                <div className="text-center">
                                                    <div className="font-medium">{supplier.purchase_orders_count}</div>
                                                    <div className="text-xs text-muted-foreground">orders</div>
                                                </div>
                                            </td>
                                            <td className="p-4">
                                                <div className="font-medium">
                                                    {supplier.credit_limit ? formatCurrency(supplier.credit_limit) : 'No limit'}
                                                </div>
                                            </td>
                                            <td className="p-4">
                                                <Badge className={getStatusColor(supplier.is_active)}>
                                                    {supplier.is_active ? 'ACTIVE' : 'INACTIVE'}
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
                                                            <Link href={`/suppliers/${supplier.id}`}>
                                                                <Eye className="h-4 w-4 mr-2" />
                                                                View
                                                            </Link>
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem asChild>
                                                            <Link href={`/suppliers/${supplier.id}/edit`}>
                                                                <Edit className="h-4 w-4 mr-2" />
                                                                Edit
                                                            </Link>
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem onClick={() => handleToggleStatus(supplier)}>
                                                            {supplier.is_active ? 'Deactivate' : 'Activate'}
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem 
                                                            onClick={() => handleDelete(supplier)}
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
                        {suppliers.last_page > 1 && (
                            <div className="flex items-center justify-between mt-6">
                                <div className="text-sm text-muted-foreground">
                                    Showing {((suppliers.current_page - 1) * suppliers.per_page) + 1} to{' '}
                                    {Math.min(suppliers.current_page * suppliers.per_page, suppliers.total)} of{' '}
                                    {suppliers.total} results
                                </div>
                                <div className="flex gap-2">
                                    {suppliers.links.map((link: any, index: number) => (
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
            <Dialog open={!!deleteSupplier} onOpenChange={() => setDeleteSupplier(null)}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Delete Supplier</DialogTitle>
                        <DialogDescription>
                            Are you sure you want to delete supplier "{deleteSupplier?.name}"? This action cannot be undone.
                            {deleteSupplier && deleteSupplier.purchase_orders_count > 0 && (
                                <div className="mt-2 p-2 bg-yellow-50 border border-yellow-200 rounded text-yellow-800">
                                    <strong>Warning:</strong> This supplier has {deleteSupplier.purchase_orders_count} purchase order(s). 
                                    You may not be able to delete this supplier.
                                </div>
                            )}
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setDeleteSupplier(null)}>
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





