import AppLayout from './app-layout';
import { type ReactNode } from 'react';

interface AuthenticatedLayoutProps {
    children: ReactNode;
    breadcrumbs?: any[];
}

export default function AuthenticatedLayout({ children, breadcrumbs, ...props }: AuthenticatedLayoutProps) {
    return (
        <AppLayout breadcrumbs={breadcrumbs} {...props}>
            {children}
        </AppLayout>
    );
}

