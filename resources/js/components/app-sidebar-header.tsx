import { Breadcrumbs } from '@/components/breadcrumbs';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { type BreadcrumbItem as BreadcrumbItemType } from '@/types';
import { Link } from '@inertiajs/react';
import { Search } from 'lucide-react';
import customers from '@/routes/customers';
import invoices from '@/routes/invoices';
import items from '@/routes/items';
import suppliers from '@/routes/suppliers';
import purchaseOrders from '@/routes/purchase-orders';
import payments from '@/routes/payments';
import accounts from '@/routes/accounts';
import users from '@/routes/users';

export function AppSidebarHeader({ breadcrumbs = [] }: { breadcrumbs?: BreadcrumbItemType[] }) {
    return (
        <header className="flex flex-col border-b border-sidebar-border/50 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12">
            {/* Top Menu Bar */}
            <div className="bg-white border-b border-gray-200 px-4 py-2">
                <div className="flex items-center justify-between">
                    {/* Left side - Menu items */}
                    <div className="flex items-center space-x-4 overflow-x-auto">
                        <span className="text-sm font-medium text-gray-700 whitespace-nowrap">File</span>
                        <span className="text-sm font-medium text-gray-700 whitespace-nowrap">Edit</span>
                        <span className="text-sm font-medium text-gray-700 whitespace-nowrap">View</span>
                        <Link href={items.index().url} className="text-sm font-medium text-gray-700 hover:text-blue-600 whitespace-nowrap">Lists</Link>
                        <span className="text-sm font-medium text-gray-700 whitespace-nowrap">Favorites</span>
                        <Link href={accounts.chartOfAccounts.index().url} className="text-sm font-medium text-gray-700 hover:text-blue-600 whitespace-nowrap">Company</Link>
                        <Link href={customers.index().url} className="text-sm font-medium text-gray-700 hover:text-blue-600 whitespace-nowrap">Customers</Link>
                        <Link href={suppliers.index().url} className="text-sm font-medium text-gray-700 hover:text-blue-600 whitespace-nowrap">Vendors</Link>
                        <Link href={users.index().url} className="text-sm font-medium text-gray-700 hover:text-blue-600 whitespace-nowrap">Employees</Link>
                        <Link href={payments.index().url} className="text-sm font-medium text-gray-700 hover:text-blue-600 whitespace-nowrap">Banking</Link>
                        <Link href={invoices.index().url} className="text-sm font-medium text-gray-700 hover:text-blue-600 whitespace-nowrap">Reports</Link>
                        <span className="text-sm font-medium text-gray-700 whitespace-nowrap">Window</span>
                        <span className="text-sm font-medium text-gray-700 whitespace-nowrap">Help</span>
                    </div>
                    
                    {/* Right side - Search and Sidebar toggle */}
                    <div className="flex items-center gap-2 ml-4">
                        {/* Search Bar */}
                        <div className="relative hidden md:block">
                            <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                            <input 
                                type="text" 
                                placeholder="Search..." 
                                className="pl-10 pr-4 py-1.5 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent w-64"
                            />
                        </div>
                        
                        {/* Sidebar Toggle */}
                        <SidebarTrigger className="p-2 hover:bg-gray-100 rounded-md transition-colors" />
                    </div>
                </div>
            </div>
            
            {/* Breadcrumbs */}
            <div className="flex h-12 shrink-0 items-center gap-2 px-4">
                <div className="flex items-center gap-2">
                    <Breadcrumbs breadcrumbs={breadcrumbs} />
                </div>
            </div>
        </header>
    );
}
