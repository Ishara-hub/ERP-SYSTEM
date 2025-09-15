import { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import { 
    Select, 
    SelectContent, 
    SelectItem, 
    SelectTrigger, 
    SelectValue 
} from '@/components/ui/select';
import { 
    ArrowLeft, 
    Save, 
    Building2, 
    TrendingUp, 
    TrendingDown, 
    Users, 
    Package,
    DollarSign
} from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Chart of Accounts', href: '/accounts/chart-of-accounts' },
    { title: 'Create Account', href: '/accounts/create' },
];

interface Account {
    id: number;
    account_code: string;
    account_name: string;
    account_type: string;
    parent_id?: number;
}

interface CreateAccountProps {
    accountTypes: Record<string, string>;
    parentAccounts: Account[];
}

export default function CreateAccount({ accountTypes, parentAccounts }: CreateAccountProps) {
    const { data, setData, post, processing, errors } = useForm({
        account_code: '',
        account_name: '',
        account_type: '',
        parent_id: null as number | null,
        opening_balance: 0,
        description: '',
        is_active: true,
        sort_order: 0,
    });

    const [showAdvanced, setShowAdvanced] = useState(false);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/accounts', {
            onSuccess: () => {
                // Redirect handled by Inertia
            }
        });
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

    const getAccountTypeDescription = (type: string) => {
        switch (type) {
            case 'Asset':
                return 'Resources owned by the company that have economic value';
            case 'Liability':
                return 'Debts and obligations owed by the company';
            case 'Equity':
                return 'Owner\'s claim on the company\'s assets after liabilities';
            case 'Income':
                return 'Revenue earned from business operations';
            case 'Expense':
                return 'Costs incurred in the course of business operations';
            default:
                return '';
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Account" />
            
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center space-x-4">
                    <Link href="/accounts/chart-of-accounts">
                        <Button variant="outline" size="sm">
                            <ArrowLeft className="h-4 w-4 mr-2" />
                            Back to Chart of Accounts
                        </Button>
                    </Link>
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Create New Account</h1>
                        <p className="text-muted-foreground">
                            Add a new account to your chart of accounts
                        </p>
                    </div>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        {/* Main Form */}
                        <div className="lg:col-span-2 space-y-6">
                            <Card>
                                <CardHeader>
                                    <CardTitle>Account Information</CardTitle>
                                    <CardDescription>
                                        Basic information about the account
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div className="space-y-2">
                                            <Label htmlFor="account_code">Account Code *</Label>
                                            <Input
                                                id="account_code"
                                                value={data.account_code}
                                                onChange={(e) => setData('account_code', e.target.value)}
                                                placeholder="e.g., 1000, 2000, 3000"
                                                className={errors.account_code ? 'border-red-500' : ''}
                                            />
                                            {errors.account_code && (
                                                <p className="text-sm text-red-500">{errors.account_code}</p>
                                            )}
                                            <p className="text-xs text-gray-500">
                                                Unique identifier for the account
                                            </p>
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor="account_name">Account Name *</Label>
                                            <Input
                                                id="account_name"
                                                value={data.account_name}
                                                onChange={(e) => setData('account_name', e.target.value)}
                                                placeholder="e.g., Cash, Accounts Payable, Sales Revenue"
                                                className={errors.account_name ? 'border-red-500' : ''}
                                            />
                                            {errors.account_name && (
                                                <p className="text-sm text-red-500">{errors.account_name}</p>
                                            )}
                                        </div>
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="account_type">Account Type *</Label>
                                        <Select 
                                            value={data.account_type} 
                                            onValueChange={(value) => setData('account_type', value)}
                                        >
                                            <SelectTrigger className={errors.account_type ? 'border-red-500' : ''}>
                                                <SelectValue placeholder="Select account type" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {Object.entries(accountTypes).map(([key, value]) => (
                                                    <SelectItem key={key} value={key}>
                                                        <div className="flex items-center space-x-2">
                                                            {getAccountTypeIcon(key)}
                                                            <span>{value}</span>
                                                        </div>
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {errors.account_type && (
                                            <p className="text-sm text-red-500">{errors.account_type}</p>
                                        )}
                                        {data.account_type && (
                                            <p className="text-xs text-gray-500">
                                                {getAccountTypeDescription(data.account_type)}
                                            </p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="parent_id">Parent Account</Label>
                                        <Select 
                                            value={data.parent_id?.toString() || 'none'} 
                                            onValueChange={(value) => setData('parent_id', value === 'none' ? null : parseInt(value))}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select parent account (optional)" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="none">No Parent Account</SelectItem>
                                                {parentAccounts.map((account) => (
                                                    <SelectItem key={account.id} value={account.id.toString()}>
                                                        <div className="flex items-center space-x-2">
                                                            {getAccountTypeIcon(account.account_type)}
                                                            <span>{account.account_name}</span>
                                                            <code className="text-xs bg-gray-100 px-1 rounded">
                                                                {account.account_code}
                                                            </code>
                                                        </div>
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        <p className="text-xs text-gray-500">
                                            Create a sub-account under an existing account
                                        </p>
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="description">Description</Label>
                                        <Textarea
                                            id="description"
                                            value={data.description}
                                            onChange={(e) => setData('description', e.target.value)}
                                            placeholder="Optional description of the account's purpose"
                                            rows={3}
                                        />
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Advanced Settings */}
                            <Card>
                                <CardHeader>
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <CardTitle>Advanced Settings</CardTitle>
                                            <CardDescription>
                                                Additional account configuration options
                                            </CardDescription>
                                        </div>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="sm"
                                            onClick={() => setShowAdvanced(!showAdvanced)}
                                        >
                                            {showAdvanced ? 'Hide' : 'Show'} Advanced
                                        </Button>
                                    </div>
                                </CardHeader>
                                {showAdvanced && (
                                    <CardContent className="space-y-4">
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div className="space-y-2">
                                                <Label htmlFor="opening_balance">Opening Balance</Label>
                                                <Input
                                                    id="opening_balance"
                                                    type="number"
                                                    step="0.01"
                                                    value={data.opening_balance}
                                                    onChange={(e) => setData('opening_balance', parseFloat(e.target.value) || 0)}
                                                    placeholder="0.00"
                                                />
                                                <p className="text-xs text-gray-500">
                                                    Initial balance for this account
                                                </p>
                                            </div>

                                            <div className="space-y-2">
                                                <Label htmlFor="sort_order">Sort Order</Label>
                                                <Input
                                                    id="sort_order"
                                                    type="number"
                                                    value={data.sort_order}
                                                    onChange={(e) => setData('sort_order', parseInt(e.target.value) || 0)}
                                                    placeholder="0"
                                                />
                                                <p className="text-xs text-gray-500">
                                                    Order in which this account appears
                                                </p>
                                            </div>
                                        </div>

                                        <div className="flex items-center space-x-2">
                                            <Switch
                                                id="is_active"
                                                checked={data.is_active}
                                                onCheckedChange={(checked) => setData('is_active', checked)}
                                            />
                                            <Label htmlFor="is_active">Active Account</Label>
                                        </div>
                                        <p className="text-xs text-gray-500">
                                            Inactive accounts won't appear in transaction forms
                                        </p>
                                    </CardContent>
                                )}
                            </Card>
                        </div>

                        {/* Sidebar */}
                        <div className="space-y-6">
                            <Card>
                                <CardHeader>
                                    <CardTitle>Account Types</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-3">
                                    {Object.entries(accountTypes).map(([key, value]) => (
                                        <div key={key} className="flex items-center space-x-2 p-2 rounded-md hover:bg-gray-50">
                                            {getAccountTypeIcon(key)}
                                            <div>
                                                <div className="font-medium text-sm">{value}</div>
                                                <div className="text-xs text-gray-500">
                                                    {getAccountTypeDescription(key)}
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader>
                                    <CardTitle>Quick Tips</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-2 text-sm text-gray-600">
                                    <p>• Use descriptive account names that clearly identify the account's purpose</p>
                                    <p>• Account codes should follow a consistent numbering system</p>
                                    <p>• Group related accounts under parent accounts for better organization</p>
                                    <p>• Keep account descriptions concise but informative</p>
                                </CardContent>
                            </Card>
                        </div>
                    </div>

                    {/* Actions */}
                    <div className="flex justify-end space-x-4">
                        <Link href="/accounts/chart-of-accounts">
                            <Button variant="outline" type="button">
                                Cancel
                            </Button>
                        </Link>
                        <Button type="submit" disabled={processing}>
                            <Save className="h-4 w-4 mr-2" />
                            {processing ? 'Creating...' : 'Create Account'}
                        </Button>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
