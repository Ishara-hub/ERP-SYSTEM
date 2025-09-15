import { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Switch } from '@/components/ui/switch';
import { 
    Plus, 
    Search, 
    Filter, 
    MoreHorizontal, 
    Edit, 
    Trash2, 
    Eye, 
    Package, 
    Wrench, 
    Settings,
    TrendingUp,
    TrendingDown,
    DollarSign
} from 'lucide-react';

interface Item {
    id: number;
    item_name: string;
    item_number: string | null;
    item_type: string;
    cost: number | string;
    sales_price: number | string;
    on_hand: number | string;
    total_value: number | string;
    is_active: boolean;
    is_inactive: boolean;
    parent?: {
        id: number;
        item_name: string;
    };
    cogs_account?: {
        account_name: string;
    };
    income_account?: {
        account_name: string;
    };
    children_count: number;
    created_at: string;
}

interface ItemsIndexProps {
    items: {
        data: Item[];
        links: any[];
        meta: any;
    };
    filters: {
        search?: string;
        item_type?: string;
        status?: string;
        parent_id?: string;
        sort_by?: string;
        sort_direction?: string;
    };
    itemTypes: string[];
    parentItems: Array<{
        id: number;
        item_name: string;
        item_type: string;
    }>;
    stats: {
        total: number;
        active: number;
        inactive: number;
        services: number;
        inventory: number;
    };
}

export default function ItemsIndex({ items, filters, itemTypes, parentItems, stats }: ItemsIndexProps) {
    const [search, setSearch] = useState(filters.search || '');
    const [itemType, setItemType] = useState(filters.item_type || 'all');
    const [status, setStatus] = useState(filters.status || 'all');
    const [parentId, setParentId] = useState(filters.parent_id || 'all');
    const [sortBy, setSortBy] = useState(filters.sort_by || 'item_name');
    const [sortDirection, setSortDirection] = useState(filters.sort_direction || 'asc');

    const handleSearch = () => {
        router.get('/items', {
            search: search || undefined,
            item_type: itemType === 'all' ? undefined : itemType || undefined,
            status: status === 'all' ? undefined : status || undefined,
            parent_id: parentId === 'all' ? undefined : parentId || undefined,
            sort_by: sortBy,
            sort_direction: sortDirection,
        }, {
            preserveState: true,
            replace: true,
        });
    };

    const handleClearFilters = () => {
        setSearch('');
        setItemType('all');
        setStatus('all');
        setParentId('all');
        setSortBy('item_name');
        setSortDirection('asc');
        router.get('/items');
    };

    const handleSort = (column: string) => {
        const newDirection = sortBy === column && sortDirection === 'asc' ? 'desc' : 'asc';
        setSortBy(column);
        setSortDirection(newDirection);
        router.get('/items', {
            ...filters,
            sort_by: column,
            sort_direction: newDirection,
        }, {
            preserveState: true,
            replace: true,
        });
    };

    const handleToggleStatus = (item: Item) => {
        router.patch(`/items/${item.id}/toggle-status`, {}, {
            preserveState: true,
        });
    };

    const getItemTypeIcon = (type: string) => {
        switch (type) {
            case 'Service':
                return <Wrench className="h-4 w-4" />;
            case 'Inventory Part':
            case 'Inventory Assembly':
                return <Package className="h-4 w-4" />;
            default:
                return <Settings className="h-4 w-4" />;
        }
    };

    const getItemTypeColor = (type: string) => {
        switch (type) {
            case 'Service':
                return 'bg-blue-50 text-blue-700 border-blue-200';
            case 'Inventory Part':
                return 'bg-green-50 text-green-700 border-green-200';
            case 'Inventory Assembly':
                return 'bg-purple-50 text-purple-700 border-purple-200';
            case 'Non-Inventory Part':
                return 'bg-orange-50 text-orange-700 border-orange-200';
            case 'Other Charge':
                return 'bg-red-50 text-red-700 border-red-200';
            case 'Discount':
                return 'bg-yellow-50 text-yellow-700 border-yellow-200';
            default:
                return 'bg-gray-50 text-gray-700 border-gray-200';
        }
    };

    return (
        <AuthenticatedLayout>
            <Head title="Items" />
            
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Items</h1>
                        <p className="text-muted-foreground">
                            Manage your inventory items, services, and assemblies
                        </p>
                    </div>
                    <div className="flex items-center gap-2">
                        <Link href="/items/create?type=Service">
                            <Button>
                                <Plus className="h-4 w-4 mr-2" />
                                Add Item
                            </Button>
                        </Link>
                    </div>
                </div>

                {/* Stats Cards */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Items</CardTitle>
                            <Package className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.total}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Active Items</CardTitle>
                            <TrendingUp className="h-4 w-4 text-green-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-green-600">{stats.active}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Inactive Items</CardTitle>
                            <TrendingDown className="h-4 w-4 text-red-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-red-600">{stats.inactive}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Services</CardTitle>
                            <Wrench className="h-4 w-4 text-blue-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-blue-600">{stats.services}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Inventory</CardTitle>
                            <Package className="h-4 w-4 text-green-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-green-600">{stats.inventory}</div>
                        </CardContent>
                    </Card>
                </div>

                {/* Filters */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Filter className="h-5 w-5" />
                            Filters
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
                            <div className="space-y-2">
                                <label className="text-sm font-medium">Search</label>
                                <div className="relative">
                                    <Search className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                                    <Input
                                        placeholder="Search items..."
                                        value={search}
                                        onChange={(e) => setSearch(e.target.value)}
                                        className="pl-10"
                                        onKeyPress={(e) => e.key === 'Enter' && handleSearch()}
                                    />
                                </div>
                            </div>
                            
                            <div className="space-y-2">
                                <label className="text-sm font-medium">Item Type</label>
                                <Select value={itemType} onValueChange={setItemType}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="All Types" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Types</SelectItem>
                                        {itemTypes.map((type) => (
                                            <SelectItem key={type} value={type}>
                                                {type}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="space-y-2">
                                <label className="text-sm font-medium">Status</label>
                                <Select value={status} onValueChange={setStatus}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="All Status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Status</SelectItem>
                                        <SelectItem value="active">Active</SelectItem>
                                        <SelectItem value="inactive">Inactive</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="space-y-2">
                                <label className="text-sm font-medium">Parent Item</label>
                                <Select value={parentId} onValueChange={setParentId}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="All Items" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Items</SelectItem>
                                        <SelectItem value="null">Top Level Only</SelectItem>
                                        {parentItems.map((item) => (
                                            <SelectItem key={item.id} value={item.id.toString()}>
                                                {item.item_name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="space-y-2">
                                <label className="text-sm font-medium">Sort By</label>
                                <Select value={sortBy} onValueChange={setSortBy}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Sort By" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="item_name">Name</SelectItem>
                                        <SelectItem value="item_type">Type</SelectItem>
                                        <SelectItem value="cost">Cost</SelectItem>
                                        <SelectItem value="sales_price">Sales Price</SelectItem>
                                        <SelectItem value="created_at">Created Date</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div className="flex items-center gap-2 mt-4">
                            <Button onClick={handleSearch}>
                                <Search className="h-4 w-4 mr-2" />
                                Search
                            </Button>
                            <Button variant="outline" onClick={handleClearFilters}>
                                Clear Filters
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                {/* Items Table */}
                <Card>
                    <CardHeader>
                        <CardTitle>Items ({items.data.length})</CardTitle>
                        <CardDescription>
                            Manage your inventory items, services, and assemblies
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {items.data.length === 0 ? (
                            <div className="text-center py-8">
                                <Package className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                                <h3 className="text-lg font-semibold mb-2">No items found</h3>
                                <p className="text-muted-foreground mb-4">
                                    {search || (itemType && itemType !== 'all') || (status && status !== 'all') || (parentId && parentId !== 'all')
                                        ? 'No items match your current filters.'
                                        : 'Get started by creating your first item.'
                                    }
                                </p>
                                <Link href="/items/create">
                                    <Button>
                                        <Plus className="h-4 w-4 mr-2" />
                                        Add Item
                                    </Button>
                                </Link>
                            </div>
                        ) : (
                            <div className="overflow-x-auto">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead 
                                                className="cursor-pointer hover:bg-muted/50"
                                                onClick={() => handleSort('item_name')}
                                            >
                                                Name
                                                {sortBy === 'item_name' && (
                                                    <span className="ml-1">
                                                        {sortDirection === 'asc' ? '↑' : '↓'}
                                                    </span>
                                                )}
                                            </TableHead>
                                            <TableHead 
                                                className="cursor-pointer hover:bg-muted/50"
                                                onClick={() => handleSort('item_type')}
                                            >
                                                Type
                                                {sortBy === 'item_type' && (
                                                    <span className="ml-1">
                                                        {sortDirection === 'asc' ? '↑' : '↓'}
                                                    </span>
                                                )}
                                            </TableHead>
                                            <TableHead>Item Number</TableHead>
                                            <TableHead 
                                                className="cursor-pointer hover:bg-muted/50"
                                                onClick={() => handleSort('cost')}
                                            >
                                                Cost
                                                {sortBy === 'cost' && (
                                                    <span className="ml-1">
                                                        {sortDirection === 'asc' ? '↑' : '↓'}
                                                    </span>
                                                )}
                                            </TableHead>
                                            <TableHead 
                                                className="cursor-pointer hover:bg-muted/50"
                                                onClick={() => handleSort('sales_price')}
                                            >
                                                Sales Price
                                                {sortBy === 'sales_price' && (
                                                    <span className="ml-1">
                                                        {sortDirection === 'asc' ? '↑' : '↓'}
                                                    </span>
                                                )}
                                            </TableHead>
                                            <TableHead>On Hand</TableHead>
                                            <TableHead>Status</TableHead>
                                            <TableHead className="text-right">Actions</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {items.data.map((item) => (
                                            <TableRow key={item.id}>
                                                <TableCell>
                                                    <div className="flex items-center gap-2">
                                                        {getItemTypeIcon(item.item_type)}
                                                        <div>
                                                            <div className="font-medium">{item.item_name}</div>
                                                            {item.parent && (
                                                                <div className="text-sm text-muted-foreground">
                                                                    Subitem of: {item.parent.item_name}
                                                                </div>
                                                            )}
                                                        </div>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <Badge className={getItemTypeColor(item.item_type)}>
                                                        {item.item_type}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell>
                                                    {item.item_number || '-'}
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex items-center gap-1">
                                                        <DollarSign className="h-3 w-3 text-muted-foreground" />
                                                        {typeof item.cost === 'number' ? item.cost.toFixed(2) : (parseFloat(item.cost) || 0).toFixed(2)}
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex items-center gap-1">
                                                        <DollarSign className="h-3 w-3 text-muted-foreground" />
                                                        {typeof item.sales_price === 'number' ? item.sales_price.toFixed(2) : (parseFloat(item.sales_price) || 0).toFixed(2)}
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    {item.item_type.includes('Inventory') ? (typeof item.on_hand === 'number' ? item.on_hand.toFixed(2) : (parseFloat(item.on_hand) || 0).toFixed(2)) : '-'}
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex items-center gap-2">
                                                        <Switch
                                                            checked={item.is_active && !item.is_inactive}
                                                            onCheckedChange={() => handleToggleStatus(item)}
                                                        />
                                                        <span className={item.is_active && !item.is_inactive ? 'text-green-600' : 'text-red-600'}>
                                                            {item.is_active && !item.is_inactive ? 'Active' : 'Inactive'}
                                                        </span>
                                                    </div>
                                                </TableCell>
                                                <TableCell className="text-right">
                                                    <DropdownMenu>
                                                        <DropdownMenuTrigger asChild>
                                                            <Button variant="ghost" size="sm">
                                                                <MoreHorizontal className="h-4 w-4" />
                                                            </Button>
                                                        </DropdownMenuTrigger>
                                                        <DropdownMenuContent align="end">
                                                            <DropdownMenuItem asChild>
                                                                <Link href={`/items/${item.id}`}>
                                                                    <Eye className="h-4 w-4 mr-2" />
                                                                    View
                                                                </Link>
                                                            </DropdownMenuItem>
                                                            <DropdownMenuItem asChild>
                                                                <Link href={`/items/${item.id}/edit`}>
                                                                    <Edit className="h-4 w-4 mr-2" />
                                                                    Edit
                                                                </Link>
                                                            </DropdownMenuItem>
                                                            <DropdownMenuItem 
                                                                onClick={() => handleToggleStatus(item)}
                                                                className="text-orange-600"
                                                            >
                                                                {item.is_active && !item.is_inactive ? 'Deactivate' : 'Activate'}
                                                            </DropdownMenuItem>
                                                        </DropdownMenuContent>
                                                    </DropdownMenu>
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AuthenticatedLayout>
    );
}
