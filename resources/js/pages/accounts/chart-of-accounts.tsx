import { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { 
    Select, 
    SelectContent, 
    SelectItem, 
    SelectTrigger, 
    SelectValue 
} from '@/components/ui/select';
import { 
    Table, 
    TableBody, 
    TableCell, 
    TableHead, 
    TableHeader, 
    TableRow 
} from '@/components/ui/table';
import { 
    Plus, 
    Search, 
    Filter, 
    Edit, 
    Trash2, 
    Eye, 
    ChevronRight,
    ChevronDown,
    DollarSign,
    Building2,
    TrendingUp,
    TrendingDown,
    Users,
    Package
} from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Chart of Accounts', href: '/accounts/chart-of-accounts' },
];

interface Account {
    id: number;
    account_code: string;
    account_name: string;
    account_type: string;
    parent_id?: number;
    opening_balance: number;
    current_balance: number;
    description?: string;
    is_active: boolean;
    is_system: boolean;
    sort_order: number;
    parent?: Account;
    children?: Account[];
    account_type_color: string;
    account_type_bg_color: string;
    full_path: string;
}

interface ChartOfAccountsProps {
    accounts: Account[];
    groupedAccounts: Record<string, Account[]>;
    accountTypes: Record<string, string>;
    parentAccounts: Account[];
    filters: {
        search?: string;
        account_type?: string;
        parent_id?: string;
    };
}

export default function ChartOfAccounts({ 
    accounts, 
    groupedAccounts, 
    accountTypes, 
    parentAccounts, 
    filters 
}: ChartOfAccountsProps) {
    const [search, setSearch] = useState(filters.search || '');
    const [accountType, setAccountType] = useState(filters.account_type || 'all');
    const [parentId, setParentId] = useState(filters.parent_id || 'all');
    const [expandedAccounts, setExpandedAccounts] = useState<Set<number>>(new Set());

    const handleSearch = () => {
        router.get('/accounts/chart-of-accounts', {
            search: search || undefined,
            account_type: accountType === 'all' ? undefined : accountType || undefined,
            parent_id: parentId === 'all' ? undefined : parentId || undefined,
        }, {
            preserveState: true,
            replace: true,
        });
    };

    const handleClearFilters = () => {
        setSearch('');
        setAccountType('all');
        setParentId('all');
        router.get('/accounts/chart-of-accounts');
    };

    const toggleExpanded = (accountId: number) => {
        const newExpanded = new Set(expandedAccounts);
        if (newExpanded.has(accountId)) {
            newExpanded.delete(accountId);
        } else {
            newExpanded.add(accountId);
        }
        setExpandedAccounts(newExpanded);
    };

    const formatCurrency = (amount: number) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
        }).format(amount);
    };

    const getAccountTypeIcon = (type: string) => {
        switch (type) {
            case 'Asset':
                return <Building2 className="h-4 w-4" />;
            case 'Liability':
                return <TrendingDown className="h-4 w-4" />;
            case 'Equity':
                return <Users className="h-4 w-4" />;
            case 'Income':
                return <TrendingUp className="h-4 w-4" />;
            case 'Expense':
                return <Package className="h-4 w-4" />;
            default:
                return <DollarSign className="h-4 w-4" />;
        }
    };

    const renderAccountRow = (account: Account, level: number = 0) => {
        const isExpanded = expandedAccounts.has(account.id);
        const hasChildren = account.children && account.children.length > 0;

        return (
            <TableRow key={account.id} className={level > 0 ? 'bg-gray-50' : ''}>
                <TableCell className="font-medium" style={{ paddingLeft: `${level * 20 + 12}px` }}>
                    <div className="flex items-center space-x-2">
                        {hasChildren && (
                            <Button
                                variant="ghost"
                                size="sm"
                                className="h-6 w-6 p-0"
                                onClick={() => toggleExpanded(account.id)}
                            >
                                {isExpanded ? (
                                    <ChevronDown className="h-4 w-4" />
                                ) : (
                                    <ChevronRight className="h-4 w-4" />
                                )}
                            </Button>
                        )}
                        {!hasChildren && <div className="w-6" />}
                        <div className="flex items-center space-x-2">
                            {getAccountTypeIcon(account.account_type)}
                            <span className="font-medium">{account.account_name}</span>
                            {account.is_system && (
                                <Badge variant="secondary" className="text-xs">System</Badge>
                            )}
                        </div>
                    </div>
                </TableCell>
                <TableCell>
                    <code className="text-sm bg-gray-100 px-2 py-1 rounded">
                        {account.account_code}
                    </code>
                </TableCell>
                <TableCell>
                    <Badge 
                        variant="outline" 
                        className={`${account.account_type_bg_color} ${account.account_type_color}`}
                    >
                        {account.account_type}
                    </Badge>
                </TableCell>
                <TableCell className="text-right">
                    {formatCurrency(account.current_balance)}
                </TableCell>
                <TableCell>
                    <div className="flex items-center space-x-2">
                        <Link
                            href={`/accounts/${account.id}`}
                            className="text-blue-600 hover:text-blue-800"
                        >
                            <Eye className="h-4 w-4" />
                        </Link>
                        <Link
                            href={`/accounts/${account.id}/edit`}
                            className="text-green-600 hover:text-green-800"
                        >
                            <Edit className="h-4 w-4" />
                        </Link>
                        {!account.is_system && (
                            <Button
                                variant="ghost"
                                size="sm"
                                className="h-8 w-8 p-0 text-red-600 hover:text-red-800"
                                onClick={() => {
                                    if (confirm('Are you sure you want to delete this account?')) {
                                        router.delete(`/accounts/${account.id}`);
                                    }
                                }}
                            >
                                <Trash2 className="h-4 w-4" />
                            </Button>
                        )}
                    </div>
                </TableCell>
            </TableRow>
        );
    };

    const renderAccountsByType = () => {
        return Object.entries(groupedAccounts).map(([type, typeAccounts]) => (
            <Card key={type} className="mb-6">
                <CardHeader>
                    <CardTitle className="flex items-center space-x-2">
                        {getAccountTypeIcon(type)}
                        <span>{accountTypes[type] || type}</span>
                        <Badge variant="outline">
                            {typeAccounts.length} accounts
                        </Badge>
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Account Name</TableHead>
                                <TableHead>Code</TableHead>
                                <TableHead>Type</TableHead>
                                <TableHead className="text-right">Balance</TableHead>
                                <TableHead>Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {typeAccounts.map(account => renderAccountRow(account))}
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        ));
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Chart of Accounts" />
            
            <div className="space-y-6">
                {/* Header */}
                <div className="flex justify-between items-center">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Chart of Accounts</h1>
                        <p className="text-muted-foreground">
                            Manage your company's chart of accounts and financial structure
                        </p>
                    </div>
                    <Link href="/accounts/create">
                        <Button>
                            <Plus className="h-4 w-4 mr-2" />
                            Add Account
                        </Button>
                    </Link>
                </div>

                {/* Filters */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center space-x-2">
                            <Filter className="h-5 w-5" />
                            <span>Filters</span>
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div className="space-y-2">
                                <label className="text-sm font-medium">Search</label>
                                <div className="relative">
                                    <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                                    <Input
                                        placeholder="Search accounts..."
                                        value={search}
                                        onChange={(e) => setSearch(e.target.value)}
                                        className="pl-10"
                                    />
                                </div>
                            </div>
                            
                            <div className="space-y-2">
                                <label className="text-sm font-medium">Account Type</label>
                                <Select value={accountType} onValueChange={setAccountType}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="All Types" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Types</SelectItem>
                                        {Object.entries(accountTypes).map(([key, value]) => (
                                            <SelectItem key={key} value={key}>
                                                {value}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="space-y-2">
                                <label className="text-sm font-medium">Parent Account</label>
                                <Select value={parentId} onValueChange={setParentId}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="All Accounts" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Accounts</SelectItem>
                                        <SelectItem value="null">Top Level Only</SelectItem>
                                        {parentAccounts.map((account) => (
                                            <SelectItem key={account.id} value={account.id.toString()}>
                                                {account.account_name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="space-y-2">
                                <label className="text-sm font-medium">&nbsp;</label>
                                <div className="flex space-x-2">
                                    <Button onClick={handleSearch} className="flex-1">
                                        <Search className="h-4 w-4 mr-2" />
                                        Search
                                    </Button>
                                    <Button variant="outline" onClick={handleClearFilters}>
                                        Clear
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Accounts by Type */}
                {Object.keys(groupedAccounts).length > 0 ? (
                    renderAccountsByType()
                ) : (
                    <Card>
                        <CardContent className="text-center py-8">
                            <Building2 className="h-12 w-12 text-gray-400 mx-auto mb-4" />
                            <h3 className="text-lg font-medium text-gray-900 mb-2">No accounts found</h3>
                            <p className="text-gray-500 mb-4">
                                {search || (accountType && accountType !== 'all') || (parentId && parentId !== 'all')
                                    ? 'No accounts match your current filters.'
                                    : 'Get started by creating your first account.'
                                }
                            </p>
                            <Link href="/accounts/create">
                                <Button>
                                    <Plus className="h-4 w-4 mr-2" />
                                    Add Account
                                </Button>
                            </Link>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AppLayout>
    );
}
