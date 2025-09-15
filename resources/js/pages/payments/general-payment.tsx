import { useState, useEffect } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { Checkbox } from '@/components/ui/checkbox';
// import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
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
    Search,
    Plus,
    Trash2,
    Copy,
    Bookmark,
    Printer,
    Paperclip,
    Clock,
    Calculator,
    RotateCcw,
    CheckCircle,
    Pen,
    BookOpen,
    ChevronDown
} from 'lucide-react';

interface BankAccount {
    id: number;
    name: string;
    account_number: string;
    balance: number;
}

interface Account {
    id: number;
    name: string;
    type: string;
}

interface Customer {
    id: number;
    name: string;
    email: string;
    address: string;
}

interface ExpenseItem {
    id: string;
    account_id: string;
    amount: string;
    memo: string;
    customer_job: string;
    billable: boolean;
}

interface GeneralPaymentProps {
    bankAccounts: BankAccount[];
    accounts: Account[];
    customers: Customer[];
}

export default function GeneralPayment({ bankAccounts, accounts, customers }: GeneralPaymentProps) {
    const { data, setData, post, processing, errors, reset } = useForm({
        bank_account_id: '',
        payee: '',
        address: '',
        memo: '',
        check_number: '',
        date: new Date().toISOString().split('T')[0],
        amount: '0.00',
        expenses: [] as ExpenseItem[],
        items: [] as ExpenseItem[],
        print_later: false,
        pay_online: false,
    });

    const [selectedTab, setSelectedTab] = useState<'expenses' | 'items'>('expenses');
    const [endingBalance, setEndingBalance] = useState(0);
    const [expensesTotal, setExpensesTotal] = useState(0);
    const [itemsTotal, setItemsTotal] = useState(0);

    // Calculate ending balance when bank account changes
    useEffect(() => {
        if (data.bank_account_id) {
            const bankAccount = bankAccounts.find(ba => ba.id.toString() === data.bank_account_id);
            if (bankAccount) {
                setEndingBalance(bankAccount.balance);
            }
        }
    }, [data.bank_account_id, bankAccounts]);

    // Calculate totals when expenses/items change
    useEffect(() => {
        const expensesSum = data.expenses.reduce((sum, item) => sum + (parseFloat(item.amount) || 0), 0);
        const itemsSum = data.items.reduce((sum, item) => sum + (parseFloat(item.amount) || 0), 0);
        setExpensesTotal(expensesSum);
        setItemsTotal(itemsSum);
        
        // Update the main amount field with the calculated total
        const totalAmount = expensesSum + itemsSum;
        if (totalAmount > 0) {
            setData('amount', totalAmount.toFixed(2));
        }
    }, [data.expenses, data.items, setData]);

    const addExpenseItem = () => {
        const newItem: ExpenseItem = {
            id: Date.now().toString(),
            account_id: '',
            amount: '0.00',
            memo: '',
            customer_job: '',
            billable: false,
        };
        setData('expenses', [...data.expenses, newItem]);
    };

    const addItem = () => {
        const newItem: ExpenseItem = {
            id: Date.now().toString(),
            account_id: '',
            amount: '0.00',
            memo: '',
            customer_job: '',
            billable: false,
        };
        setData('items', [...data.items, newItem]);
    };

    const removeExpenseItem = (id: string) => {
        setData('expenses', data.expenses.filter(item => item.id !== id));
    };

    const removeItem = (id: string) => {
        setData('items', data.items.filter(item => item.id !== id));
    };

    const updateExpenseItem = (id: string, field: keyof ExpenseItem, value: string | boolean) => {
        setData('expenses', data.expenses.map(item => 
            item.id === id ? { ...item, [field]: value } : item
        ));
    };

    const updateItem = (id: string, field: keyof ExpenseItem, value: string | boolean) => {
        setData('items', data.items.map(item => 
            item.id === id ? { ...item, [field]: value } : item
        ));
    };

    const clearForm = () => {
        reset();
        setExpensesTotal(0);
        setItemsTotal(0);
        setEndingBalance(0);
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        
        // Validate required fields
        if (!data.bank_account_id) {
            alert('Please select a bank account');
            return;
        }
        
        if (!data.payee) {
            alert('Please enter a payee name');
            return;
        }
        
        // Calculate total from expenses and items
        const totalExpenses = data.expenses.reduce((sum, item) => sum + (parseFloat(item.amount) || 0), 0);
        const totalItems = data.items.reduce((sum, item) => sum + (parseFloat(item.amount) || 0), 0);
        const totalAmount = totalExpenses + totalItems;
        
        if (totalAmount <= 0) {
            alert('Please add at least one expense or item with a valid amount');
            return;
        }
        
        // Update the amount to match the calculated total
        const formData = {
            ...data,
            amount: totalAmount.toString()
        };
        
        post('/payments/general', {
            data: formData,
            onSuccess: () => {
                clearForm();
                alert('Payment recorded successfully!');
            },
            onError: (errors) => {
                console.error('Form submission errors:', errors);
            }
        });
    };

    const formatCurrency = (amount: number) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(amount);
    };

    const convertToWords = (amount: number) => {
        // Simple number to words conversion (you might want to use a library for this)
        const ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine'];
        const tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        const teens = ['Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        
        if (amount === 0) return 'Zero Dollars';
        
        let dollars = Math.floor(amount);
        const cents = Math.round((amount - dollars) * 100);
        
        let result = '';
        
        if (dollars > 0) {
            if (dollars >= 1000) {
                result += ones[Math.floor(dollars / 1000)] + ' Thousand ';
                dollars %= 1000;
            }
            if (dollars >= 100) {
                result += ones[Math.floor(dollars / 100)] + ' Hundred ';
                dollars %= 100;
            }
            if (dollars >= 20) {
                result += tens[Math.floor(dollars / 10)] + ' ';
                dollars %= 10;
            } else if (dollars >= 10) {
                result += teens[dollars - 10] + ' ';
                dollars = 0;
            }
            if (dollars > 0) {
                result += ones[dollars] + ' ';
            }
            result += 'Dollars';
        }
        
        if (cents > 0) {
            result += ' and ' + cents + ' Cents';
        }
        
        return result;
    };

    return (
        <AppLayout breadcrumbs={[
            { title: 'Payments', href: '/payments' },
            { title: 'General Payment', href: '/payments/general' }
        ]}>
            <Head title="General Payment" />
            
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
                            <h1 className="text-3xl font-bold tracking-tight">General Payment</h1>
                            <p className="text-muted-foreground">
                                Record a general payment or write a check
                            </p>
                        </div>
                    </div>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    {/* Error Display */}
                    {Object.keys(errors).length > 0 && (
                        <div className="bg-red-50 border border-red-200 rounded-lg p-4">
                            <div className="flex">
                                <div className="ml-3">
                                    <h3 className="text-sm font-medium text-red-800">
                                        There were errors with your submission
                                    </h3>
                                    <div className="mt-2 text-sm text-red-700">
                                        <ul className="list-disc pl-5 space-y-1">
                                            {Object.entries(errors).map(([key, value]) => (
                                                <li key={key}>{value}</li>
                                            ))}
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Top Toolbar */}
                    <div className="bg-white border border-gray-200 rounded-lg p-4">
                        <div className="flex items-center justify-between flex-wrap gap-4">
                            <div className="flex items-center gap-2">
                                <Button type="button" variant="outline" size="sm">
                                    <Search className="h-4 w-4 mr-2" />
                                    Find
                                </Button>
                                <Button type="button" variant="outline" size="sm">
                                    <Plus className="h-4 w-4 mr-2" />
                                    New
                                </Button>
                                <Button type="submit" variant="outline" size="sm" disabled={processing}>
                                    <Save className="h-4 w-4 mr-2" />
                                    {processing ? 'Saving...' : 'Save'}
                                </Button>
                                <Button type="button" variant="outline" size="sm">
                                    <Trash2 className="h-4 w-4 mr-2" />
                                    Delete
                                </Button>
                                <Button type="button" variant="outline" size="sm">
                                    <Copy className="h-4 w-4 mr-2" />
                                    Create a Copy
                                </Button>
                                <Button type="button" variant="outline" size="sm">
                                    <Bookmark className="h-4 w-4 mr-2" />
                                    Memorize
                                </Button>
                            </div>
                            
                            <div className="flex items-center gap-2">
                                <Button type="button" variant="outline" size="sm">
                                    <Printer className="h-4 w-4 mr-2" />
                                    Print
                                </Button>
                                <div className="flex items-center gap-2">
                                    <Checkbox 
                                        id="print_later" 
                                        checked={data.print_later}
                                        onCheckedChange={(checked) => setData('print_later', checked as boolean)}
                                    />
                                    <Label htmlFor="print_later" className="text-sm">Print Later</Label>
                                </div>
                                <div className="flex items-center gap-2">
                                    <Checkbox 
                                        id="pay_online" 
                                        checked={data.pay_online}
                                        onCheckedChange={(checked) => setData('pay_online', checked as boolean)}
                                    />
                                    <Label htmlFor="pay_online" className="text-sm">Pay Online</Label>
                                </div>
                            </div>
                            
                            <div className="flex items-center gap-2">
                                <Button type="button" variant="outline" size="sm">
                                    <Paperclip className="h-4 w-4 mr-2" />
                                    Attach File
                                </Button>
                                <Button type="button" variant="outline" size="sm">
                                    <FileText className="h-4 w-4 mr-2" />
                                    Select PO
                                </Button>
                                <Button type="button" variant="outline" size="sm">
                                    <Clock className="h-4 w-4 mr-2" />
                                    Enter Time
                                </Button>
                                <Button type="button" variant="outline" size="sm">
                                    <Calculator className="h-4 w-4 mr-2" />
                                    Clear Splits
                                </Button>
                                <Button type="button" variant="outline" size="sm">
                                    <RotateCcw className="h-4 w-4 mr-2" />
                                    Recalculate
                                </Button>
                            </div>
                            
                            <div className="flex items-center gap-2">
                                <Button type="button" variant="outline" size="sm">
                                    <FileText className="h-4 w-4 mr-2" />
                                    Batch Transactions
                                </Button>
                                <Button type="button" variant="outline" size="sm">
                                    <Clock className="h-4 w-4 mr-2" />
                                    Reorder Reminder
                                </Button>
                                <Button type="button" variant="outline" size="sm">
                                    <CheckCircle className="h-4 w-4 mr-2" />
                                    Order Checks
                                </Button>
                            </div>
                        </div>
                    </div>

                    {/* Bank Account and Balance */}
                    <div className="bg-white border border-gray-200 rounded-lg p-4">
                        <div className="flex items-center justify-between">
                            <div className="flex items-center gap-4">
                                <Label htmlFor="bank_account_id" className="text-sm font-medium">BANK ACCOUNT</Label>
                                <Select 
                                    value={data.bank_account_id} 
                                    onValueChange={(value) => setData('bank_account_id', value)}
                                >
                                    <SelectTrigger className="w-80">
                                        <SelectValue placeholder="Select bank account" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {bankAccounts.map((account) => (
                                            <SelectItem key={account.id} value={account.id.toString()}>
                                                {account.account_number} · {account.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="flex items-center gap-4">
                                <Label className="text-sm font-medium">ENDING BALANCE</Label>
                                <span className="text-lg font-semibold">{formatCurrency(endingBalance)}</span>
                            </div>
                        </div>
                    </div>

                    {/* Check-like Payment Form */}
                    <div className="bg-gradient-to-r from-green-50 to-green-100 border-2 border-green-300 rounded-lg p-6">
                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            {/* Left side - Payee info */}
                            <div className="space-y-4">
                                <div>
                                    <Label htmlFor="payee" className="text-sm font-medium text-green-800">PAY TO THE ORDER OF</Label>
                                    <Input
                                        id="payee"
                                        value={data.payee}
                                        onChange={(e) => setData('payee', e.target.value)}
                                        className="mt-1 border-green-300 focus:border-green-500"
                                        placeholder="Enter payee name"
                                    />
                                </div>
                                
                                <div>
                                    <Label htmlFor="address" className="text-sm font-medium text-green-800">ADDRESS</Label>
                                    <Textarea
                                        id="address"
                                        value={data.address}
                                        onChange={(e) => setData('address', e.target.value)}
                                        className="mt-1 border-green-300 focus:border-green-500"
                                        placeholder="Enter payee address"
                                        rows={3}
                                    />
                                </div>
                                
                                <div>
                                    <Label htmlFor="memo" className="text-sm font-medium text-green-800">MEMO</Label>
                                    <Input
                                        id="memo"
                                        value={data.memo}
                                        onChange={(e) => setData('memo', e.target.value)}
                                        className="mt-1 border-green-300 focus:border-green-500"
                                        placeholder="Enter memo"
                                    />
                                </div>
                            </div>

                            {/* Right side - Check details */}
                            <div className="space-y-4">
                                <div className="flex items-center gap-4">
                                    <div>
                                        <Label htmlFor="check_number" className="text-sm font-medium text-green-800">NO.</Label>
                                        <Input
                                            id="check_number"
                                            value={data.check_number}
                                            onChange={(e) => setData('check_number', e.target.value)}
                                            className="mt-1 w-24 border-green-300 focus:border-green-500"
                                            placeholder="000"
                                        />
                                    </div>
                                    <div className="flex items-center">
                                        <Checkbox id="to_print" />
                                        <Label htmlFor="to_print" className="ml-2 text-sm font-medium text-green-800">TO PRINT</Label>
                                    </div>
                                </div>
                                
                                <div>
                                    <Label htmlFor="date" className="text-sm font-medium text-green-800">DATE</Label>
                                    <div className="relative mt-1">
                                        <Calendar className="absolute left-3 top-3 h-4 w-4 text-green-600" />
                                        <Input
                                            id="date"
                                            type="date"
                                            value={data.date}
                                            onChange={(e) => setData('date', e.target.value)}
                                            className="pl-10 border-green-300 focus:border-green-500"
                                        />
                                    </div>
                                </div>
                                
                                <div className="flex items-center gap-2">
                                    <span className="text-2xl font-bold text-green-800">$</span>
                                    <Input
                                        id="amount"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        value={data.amount}
                                        onChange={(e) => setData('amount', e.target.value)}
                                        className="text-2xl font-bold border-green-300 focus:border-green-500"
                                        placeholder="0.00"
                                    />
                                </div>
                                
                                <div>
                                    <Label className="text-sm font-medium text-green-800">DOLLARS</Label>
                                    <div className="mt-1 p-2 bg-white border border-green-300 rounded text-sm text-gray-700 min-h-[2rem]">
                                        {convertToWords(parseFloat(data.amount) || 0)}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Expenses/Items Section */}
                    <div className="bg-white border border-gray-200 rounded-lg">
                        <div className="border-b border-gray-200">
                            <div className="flex">
                                <button
                                    type="button"
                                    onClick={() => setSelectedTab('expenses')}
                                    className={`flex-1 flex items-center justify-center gap-2 px-4 py-3 text-sm font-medium border-b-2 transition-colors ${
                                        selectedTab === 'expenses' 
                                            ? 'border-blue-500 text-blue-600 bg-blue-50' 
                                            : 'border-transparent text-gray-500 hover:text-gray-700'
                                    }`}
                                >
                                    Expenses
                                    <Badge variant="secondary">{formatCurrency(expensesTotal)}</Badge>
                                </button>
                                <button
                                    type="button"
                                    onClick={() => setSelectedTab('items')}
                                    className={`flex-1 flex items-center justify-center gap-2 px-4 py-3 text-sm font-medium border-b-2 transition-colors ${
                                        selectedTab === 'items' 
                                            ? 'border-blue-500 text-blue-600 bg-blue-50' 
                                            : 'border-transparent text-gray-500 hover:text-gray-700'
                                    }`}
                                >
                                    Items
                                    <Badge variant="secondary">{formatCurrency(itemsTotal)}</Badge>
                                </button>
                            </div>
                        </div>
                        
                        {selectedTab === 'expenses' && (
                                <div className="p-4">
                                    <div className="flex justify-between items-center mb-4">
                                        <h3 className="text-lg font-semibold">Expenses</h3>
                                        <Button type="button" onClick={addExpenseItem} size="sm">
                                            <Plus className="h-4 w-4 mr-2" />
                                            Add Expense
                                        </Button>
                                    </div>
                                    
                                    <div className="overflow-x-auto">
                                        <table className="w-full">
                                            <thead>
                                                <tr className="border-b border-gray-200">
                                                    <th className="text-left p-2 font-medium">ACCOUNT</th>
                                                    <th className="text-left p-2 font-medium">AMOUNT</th>
                                                    <th className="text-left p-2 font-medium">MEMO</th>
                                                    <th className="text-left p-2 font-medium">CUSTOMER:JOB</th>
                                                    <th className="text-left p-2 font-medium">BILLABLE</th>
                                                    <th className="text-left p-2 font-medium">ACTIONS</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {data.expenses.map((item, index) => (
                                                    <tr key={item.id} className={index % 2 === 0 ? 'bg-blue-50' : 'bg-white'}>
                                                        <td className="p-2">
                                                            <Select 
                                                                value={item.account_id} 
                                                                onValueChange={(value) => updateExpenseItem(item.id, 'account_id', value)}
                                                            >
                                                                <SelectTrigger className="w-full">
                                                                    <SelectValue placeholder="Select account" />
                                                                </SelectTrigger>
                                                                <SelectContent>
                                                                    {accounts.map((account) => (
                                                                        <SelectItem key={account.id} value={account.id.toString()}>
                                                                            {account.name}
                                                                        </SelectItem>
                                                                    ))}
                                                                </SelectContent>
                                                            </Select>
                                                        </td>
                                                        <td className="p-2">
                                                            <Input
                                                                type="number"
                                                                step="0.01"
                                                                value={item.amount}
                                                                onChange={(e) => updateExpenseItem(item.id, 'amount', e.target.value)}
                                                                className="w-full"
                                                            />
                                                        </td>
                                                        <td className="p-2">
                                                            <Input
                                                                value={item.memo}
                                                                onChange={(e) => updateExpenseItem(item.id, 'memo', e.target.value)}
                                                                className="w-full"
                                                            />
                                                        </td>
                                                        <td className="p-2">
                                                            <Select 
                                                                value={item.customer_job} 
                                                                onValueChange={(value) => updateExpenseItem(item.id, 'customer_job', value)}
                                                            >
                                                                <SelectTrigger className="w-full">
                                                                    <SelectValue placeholder="Select customer" />
                                                                </SelectTrigger>
                                                                <SelectContent>
                                                                    {customers.map((customer) => (
                                                                        <SelectItem key={customer.id} value={customer.id.toString()}>
                                                                            {customer.name}
                                                                        </SelectItem>
                                                                    ))}
                                                                </SelectContent>
                                                            </Select>
                                                        </td>
                                                        <td className="p-2">
                                                            <Checkbox 
                                                                checked={item.billable}
                                                                onCheckedChange={(checked) => updateExpenseItem(item.id, 'billable', checked as boolean)}
                                                            />
                                                        </td>
                                                        <td className="p-2">
                                                            <Button 
                                                                type="button" 
                                                                variant="outline" 
                                                                size="sm"
                                                                onClick={() => removeExpenseItem(item.id)}
                                                            >
                                                                <Trash2 className="h-4 w-4" />
                                                            </Button>
                                                        </td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                        )}
                        
                        {selectedTab === 'items' && (
                                <div className="p-4">
                                    <div className="flex justify-between items-center mb-4">
                                        <h3 className="text-lg font-semibold">Items</h3>
                                        <Button type="button" onClick={addItem} size="sm">
                                            <Plus className="h-4 w-4 mr-2" />
                                            Add Item
                                        </Button>
                                    </div>
                                    
                                    <div className="overflow-x-auto">
                                        <table className="w-full">
                                            <thead>
                                                <tr className="border-b border-gray-200">
                                                    <th className="text-left p-2 font-medium">ACCOUNT</th>
                                                    <th className="text-left p-2 font-medium">AMOUNT</th>
                                                    <th className="text-left p-2 font-medium">MEMO</th>
                                                    <th className="text-left p-2 font-medium">CUSTOMER:JOB</th>
                                                    <th className="text-left p-2 font-medium">BILLABLE</th>
                                                    <th className="text-left p-2 font-medium">ACTIONS</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {data.items.map((item, index) => (
                                                    <tr key={item.id} className={index % 2 === 0 ? 'bg-blue-50' : 'bg-white'}>
                                                        <td className="p-2">
                                                            <Select 
                                                                value={item.account_id} 
                                                                onValueChange={(value) => updateItem(item.id, 'account_id', value)}
                                                            >
                                                                <SelectTrigger className="w-full">
                                                                    <SelectValue placeholder="Select account" />
                                                                </SelectTrigger>
                                                                <SelectContent>
                                                                    {accounts.map((account) => (
                                                                        <SelectItem key={account.id} value={account.id.toString()}>
                                                                            {account.name}
                                                                        </SelectItem>
                                                                    ))}
                                                                </SelectContent>
                                                            </Select>
                                                        </td>
                                                        <td className="p-2">
                                                            <Input
                                                                type="number"
                                                                step="0.01"
                                                                value={item.amount}
                                                                onChange={(e) => updateItem(item.id, 'amount', e.target.value)}
                                                                className="w-full"
                                                            />
                                                        </td>
                                                        <td className="p-2">
                                                            <Input
                                                                value={item.memo}
                                                                onChange={(e) => updateItem(item.id, 'memo', e.target.value)}
                                                                className="w-full"
                                                            />
                                                        </td>
                                                        <td className="p-2">
                                                            <Select 
                                                                value={item.customer_job} 
                                                                onValueChange={(value) => updateItem(item.id, 'customer_job', value)}
                                                            >
                                                                <SelectTrigger className="w-full">
                                                                    <SelectValue placeholder="Select customer" />
                                                                </SelectTrigger>
                                                                <SelectContent>
                                                                    {customers.map((customer) => (
                                                                        <SelectItem key={customer.id} value={customer.id.toString()}>
                                                                            {customer.name}
                                                                        </SelectItem>
                                                                    ))}
                                                                </SelectContent>
                                                            </Select>
                                                        </td>
                                                        <td className="p-2">
                                                            <Checkbox 
                                                                checked={item.billable}
                                                                onCheckedChange={(checked) => updateItem(item.id, 'billable', checked as boolean)}
                                                            />
                                                        </td>
                                                        <td className="p-2">
                                                            <Button 
                                                                type="button" 
                                                                variant="outline" 
                                                                size="sm"
                                                                onClick={() => removeItem(item.id)}
                                                            >
                                                                <Trash2 className="h-4 w-4" />
                                                            </Button>
                                                        </td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                        )}
                    </div>

                    {/* Bottom Action Buttons */}
                    <div className="flex justify-end gap-4">
                        <Button type="button" variant="outline" onClick={clearForm}>
                            <X className="h-4 w-4 mr-2" />
                            Clear
                        </Button>
                        <Button type="submit" variant="outline" disabled={processing}>
                            <Save className="h-4 w-4 mr-2" />
                            Save & Close
                        </Button>
                        <Button type="submit" className="bg-blue-600 hover:bg-blue-700" disabled={processing}>
                            <Save className="h-4 w-4 mr-2" />
                            Save & New
                        </Button>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
