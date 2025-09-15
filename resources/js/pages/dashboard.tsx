import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { 
    Users, ShoppingCart, Package, FileText, AlertCircle, 
    Search, Home, Building2, BarChart3, Calendar, Camera, 
    Eye, Star, Monitor, ArrowRight, CheckCircle, Clock, 
    CreditCard, Receipt, Banknote, Smartphone, 
    Grid3X3, Box, Tag, PiggyBank, Printer, Pen, BookOpen
} from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import customers from '@/routes/customers';
import invoices from '@/routes/invoices';
import items from '@/routes/items';
import suppliers from '@/routes/suppliers';
import purchaseOrders from '@/routes/purchase-orders';
import payments from '@/routes/payments';
import accounts from '@/routes/accounts';
import users from '@/routes/users';
import roles from '@/routes/roles';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

interface DashboardProps {
    stats: {
        total_employees: number;
        total_customers: number;
        total_products: number;
        total_invoices: number;
        total_purchase_orders: number;
        total_sales_orders: number;
        pending_invoices: number;
        pending_purchase_orders: number;
        pending_sales_orders: number;
    };
    recent_employees: Array<{
        id: number;
        name: string;
        email: string;
        department: { name: string };
        designation: { name: string };
        status: string;
    }>;
    recent_customers: Array<{
        id: number;
        name: string;
        email: string;
        phone: string;
    }>;
    recent_invoices: Array<{
        id: number;
        invoice_no: string;
        total_amount: number | string;
        status: string;
        customer: { name: string };
    }>;
}

export default function Dashboard({ stats, recent_employees, recent_customers, recent_invoices }: DashboardProps) {
    // Helper function to safely format currency
    const formatCurrency = (amount: number | string): string => {
        const numAmount = typeof amount === 'string' ? parseFloat(amount) : amount;
        return isNaN(numAmount) ? '0.00' : numAmount.toFixed(2);
    };

    // QuickBooks-style workflow processes
    const vendorWorkflow = [
        { name: 'Purchase Orders', icon: FileText, color: 'bg-green-500', href: purchaseOrders.index().url },
        { name: 'Receive Inventory', icon: Package, color: 'bg-yellow-500', href: purchaseOrders.index().url },
        { name: 'Enter Bills Against Inventory', icon: Receipt, color: 'bg-blue-500', href: suppliers.index().url },
        { name: 'Enter Bills', icon: Receipt, color: 'bg-blue-500', href: suppliers.index().url },
        { name: 'Pay Bills', icon: Banknote, color: 'bg-blue-500', href: payments.index().url }
    ];

    const customerWorkflow = [
        { name: 'Estimates', icon: FileText, color: 'bg-green-500', href: invoices.create().url },
        { name: 'Sales Orders', icon: ShoppingCart, color: 'bg-blue-500', href: purchaseOrders.index().url },
        { name: 'Create Invoices', icon: FileText, color: 'bg-blue-500', href: invoices.create().url },
        { name: 'Receive Payments', icon: Banknote, color: 'bg-green-500', href: payments.create().url },

    ];

    const employeeWorkflow = [
        { name: 'Enter Time', icon: Clock, color: 'bg-green-500', href: users.index().url }
    ];

    const companyActions = [
        { name: 'Chart of Accounts', icon: Grid3X3, color: 'text-blue-600', href: accounts.chartOfAccounts.index().url },
        { name: 'Inventory Activities', icon: Box, color: 'text-blue-600', href: items.index().url },
        { name: 'Items & Services', icon: Tag, color: 'text-yellow-600', href: items.index().url },
        { name: 'ERP Financing', icon: PiggyBank, color: 'text-blue-600', href: payments.index().url },
        { name: 'Web and Mobile Apps', icon: Smartphone, color: 'text-blue-600', href: '#' },
        { name: 'Calendar', icon: Calendar, color: 'text-blue-600', href: '#' }
    ];

    const bankingActions = [
        { name: 'Record Deposits', icon: Banknote, color: 'text-yellow-600', href: payments.create().url },
        { name: 'General Payment', icon: Receipt, color: 'text-blue-600', href: '/payments/general' },
        { name: 'Reconcile', icon: CheckCircle, color: 'text-blue-600', href: payments.index().url },
        { name: 'Write Checks', icon: Pen, color: 'text-blue-600', href: payments.create().url },
        { name: 'Check Register', icon: BookOpen, color: 'text-green-600', href: payments.index().url },
        { name: 'Print Checks', icon: Printer, color: 'text-blue-600', href: payments.index().url }
    ];

    const myShortcuts = [
        { name: 'Dashboard', icon: Home, active: true, href: dashboard().url },
        { name: 'Customers', icon: Users, href: customers.index().url },
        { name: 'Invoices', icon: FileText, href: invoices.index().url },
        { name: 'Items', icon: Package, href: items.index().url },
        { name: 'Suppliers', icon: Building2, href: suppliers.index().url },
        { name: 'Purchase Orders', icon: ShoppingCart, href: purchaseOrders.index().url },
        { name: 'Payments', icon: CreditCard, href: payments.index().url },
        { name: 'Chart of Accounts', icon: BarChart3, href: accounts.chartOfAccounts.index().url }
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="ERP Dashboard" />
            <div className="flex h-screen bg-gray-50">
                {/* Left Sidebar - QuickBooks Style */}
                

                {/* Main Content Area */}
                <div className="flex-1 flex flex-col">
                    {/* Top Menu Bar */}
                    

                    {/* Main Dashboard Content */}
                    <div className="flex-1 p-6">
                        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 h-full">
                            {/* Left Column - Workflow Processes */}
                            <div className="lg:col-span-2 space-y-6">
                                {/* VENDORS Section */}
                                <div className="bg-white rounded-lg border border-gray-200">
                                    <div className="bg-blue-100 px-4 py-3 rounded-t-lg">
                                        <h2 className="text-lg font-semibold text-blue-900">VENDORS</h2>
                                    </div>
                                    <div className="p-6">
                                        <div className="flex items-center space-x-4 overflow-x-auto">
                                            {vendorWorkflow.map((step, index) => (
                                                <div key={index} className="flex items-center">
                                                    <Link href={step.href} className="flex flex-col items-center space-y-2 hover:opacity-80 transition-opacity">
                                                        <div className={`w-12 h-12 rounded-full ${step.color} flex items-center justify-center text-white`}>
                                                            <step.icon className="h-6 w-6" />
                                                        </div>
                                                        <span className="text-xs text-center text-gray-700 max-w-20">{step.name}</span>
                                                    </Link>
                                                    {index < vendorWorkflow.length - 1 && (
                                                        <ArrowRight className="h-4 w-4 text-gray-400 mx-2" />
                                                    )}
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                </div>

                                {/* CUSTOMERS Section */}
                                <div className="bg-white rounded-lg border border-gray-200">
                                    <div className="bg-blue-100 px-4 py-3 rounded-t-lg">
                                        <h2 className="text-lg font-semibold text-blue-900">CUSTOMERS</h2>
                                    </div>
                                    <div className="p-6">
                                        <div className="grid grid-cols-2 gap-4">
                                            {/* Main workflow */}
                                            <div className="flex items-center space-x-4 overflow-x-auto">
                                                {customerWorkflow.slice(0, 4).map((step, index) => (
                                                    <div key={index} className="flex items-center">
                                                        <Link href={step.href} className="flex flex-col items-center space-y-2 hover:opacity-80 transition-opacity">
                                                            <div className={`w-12 h-12 rounded-full ${step.color} flex items-center justify-center text-white`}>
                                                                <step.icon className="h-6 w-6" />
                                                            </div>
                                                            <span className="text-xs text-center text-gray-700 max-w-20">{step.name}</span>
                                                        </Link>
                                                        {index < 3 && (
                                                            <ArrowRight className="h-4 w-4 text-gray-400 mx-2" />
                                                        )}
                                                    </div>
                                                ))}
                                            </div>
                                            {/* Additional actions */}
                                            <div className="space-y-2">
                                                {customerWorkflow.slice(4).map((step, index) => (
                                                    <Link key={index + 4} href={step.href} className="flex items-center space-x-2 hover:opacity-80 transition-opacity">
                                                        <div className={`w-8 h-8 rounded-full ${step.color} flex items-center justify-center text-white`}>
                                                            <step.icon className="h-4 w-4" />
                                                        </div>
                                                        <span className="text-xs text-gray-700">{step.name}</span>
                                                    </Link>
                                                ))}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {/* EMPLOYEES Section */}
                                <div className="bg-white rounded-lg border border-gray-200">
                                    <div className="bg-blue-100 px-4 py-3 rounded-t-lg">
                                        <h2 className="text-lg font-semibold text-blue-900">EMPLOYEES</h2>
                                    </div>
                                    <div className="p-6">
                                        <div className="flex items-center space-x-4">
                                            {employeeWorkflow.map((step, index) => (
                                                <Link key={index} href={step.href} className="flex flex-col items-center space-y-2 hover:opacity-80 transition-opacity">
                                                    <div className={`w-12 h-12 rounded-full ${step.color} flex items-center justify-center text-white`}>
                                                        <step.icon className="h-6 w-6" />
                                                    </div>
                                                    <span className="text-xs text-center text-gray-700">{step.name}</span>
                                                </Link>
                                            ))}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Right Column - Company & Banking */}
                            <div className="space-y-6">
                                {/* COMPANY Section */}
                                <div className="bg-white rounded-lg border border-gray-200">
                                    <div className="bg-blue-100 px-4 py-3 rounded-t-lg">
                                        <h2 className="text-lg font-semibold text-blue-900">COMPANY</h2>
                                    </div>
                                    <div className="p-4">
                                        <div className="space-y-3">
                                            {companyActions.map((action, index) => (
                                                <Link
                                                    key={index}
                                                    href={action.href}
                                                    className="w-full flex items-center space-x-3 p-2 rounded-md hover:bg-gray-50 transition-colors"
                                                >
                                                    <action.icon className={`h-5 w-5 ${action.color}`} />
                                                    <span className="text-sm text-gray-700">{action.name}</span>
                                                </Link>
                                            ))}
                                        </div>
                                    </div>
                                </div>

                                {/* BANKING Section */}
                                <div className="bg-white rounded-lg border border-gray-200">
                                    <div className="bg-blue-100 px-4 py-3 rounded-t-lg">
                                        <h2 className="text-lg font-semibold text-blue-900">BANKING</h2>
                                    </div>
                                    <div className="p-4">
                                        <div className="space-y-3">
                                            {bankingActions.map((action, index) => (
                                                <Link
                                                    key={index}
                                                    href={action.href}
                                                    className="w-full flex items-center space-x-3 p-2 rounded-md hover:bg-gray-50 transition-colors"
                                                >
                                                    <action.icon className={`h-5 w-5 ${action.color}`} />
                                                    <span className="text-sm text-gray-700">{action.name}</span>
                                                </Link>
                                            ))}
                                        </div>
                                    </div>
                                </div>

                                {/* Quick Stats */}
                                <div className="bg-white rounded-lg border border-gray-200">
                                    <div className="bg-blue-100 px-4 py-3 rounded-t-lg">
                                        <h2 className="text-lg font-semibold text-blue-900">QUICK STATS</h2>
                                    </div>
                                    <div className="p-4 space-y-3">
                                        <div className="flex justify-between items-center">
                                            <span className="text-sm text-gray-600">Total Employees</span>
                                            <span className="text-lg font-semibold text-blue-600">{stats.total_employees}</span>
                                        </div>
                                        <div className="flex justify-between items-center">
                                            <span className="text-sm text-gray-600">Total Customers</span>
                                            <span className="text-lg font-semibold text-green-600">{stats.total_customers}</span>
                                        </div>
                                        <div className="flex justify-between items-center">
                                            <span className="text-sm text-gray-600">Total Products</span>
                                            <span className="text-lg font-semibold text-purple-600">{stats.total_products}</span>
                                        </div>
                                        <div className="flex justify-between items-center">
                                            <span className="text-sm text-gray-600">Total Invoices</span>
                                            <span className="text-lg font-semibold text-orange-600">{stats.total_invoices}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
