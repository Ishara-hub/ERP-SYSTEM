import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/react';
import { BookOpen, Folder, LayoutGrid, Users, FileText, Package, Building2, ShoppingCart, CreditCard, BarChart3, AlertCircle } from 'lucide-react';
import AppLogo from './app-logo';
import customers from '@/routes/customers';
import invoices from '@/routes/invoices';
import items from '@/routes/items';
import suppliers from '@/routes/suppliers';
import purchaseOrders from '@/routes/purchase-orders';
import payments from '@/routes/payments';
import accounts from '@/routes/accounts';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Customers',
        href: customers.index(),
        icon: Users,
    },
    {
        title: 'Invoices',
        href: invoices.index(),
        icon: FileText,
    },
    {
        title: 'Items',
        href: items.index(),
        icon: Package,
    },
    {
        title: 'Suppliers',
        href: suppliers.index(),
        icon: Building2,
    },
    {
        title: 'Purchase Orders',
        href: purchaseOrders.index(),
        icon: ShoppingCart,
    },
    {
        title: 'Payments',
        href: payments.index(),
        icon: CreditCard,
    },
    {
        title: 'Chart of Accounts',
        href: accounts.chartOfAccounts.index(),
        icon: BarChart3,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/react-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#react',
        icon: BookOpen,
    },
];

export function AppSidebar() {
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
                
                {/* System Alert Section */}
                <div className="p-4 border-t border-sidebar-border/50">
                    <div className="bg-red-50 border border-red-200 rounded-lg p-3">
                        <div className="flex items-center space-x-2 mb-2">
                            <AlertCircle className="h-4 w-4 text-red-600" />
                            <span className="text-sm font-medium text-red-800">System Alert</span>
                        </div>
                        <p className="text-xs text-red-700 mb-2">
                            Welcome to your new ERP system! All modules are ready to use.
                        </p>
                        <Link 
                            href={dashboard()} 
                            className="inline-flex items-center px-2 py-1 text-xs font-medium text-red-800 bg-red-100 hover:bg-red-200 rounded transition-colors"
                        >
                            Get Started
                        </Link>
                    </div>
                </div>
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
