import { Head } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Printer, ArrowLeft } from 'lucide-react';
import { Link } from '@inertiajs/react';

interface Invoice {
    id: number;
    invoice_no: string;
    date: string;
    ship_date: string;
    total_amount: number;
    subtotal: number;
    tax_amount: number;
    discount_amount: number;
    shipping_amount: number;
    payments_applied: number;
    balance_due: number;
    status: string;
    po_number: string;
    terms: string;
    rep: string;
    via: string;
    fob: string;
    customer_message: string;
    memo: string;
    billing_address: string;
    shipping_address: string;
    template: string;
    is_online_payment_enabled: boolean;
    created_at: string;
    updated_at: string;
    customer: {
        id: number;
        name: string;
        email: string;
        phone: string;
        address: string;
    };
    line_items: Array<{
        id: number;
        description: string;
        quantity: number;
        unit_price: number;
        amount: number;
        tax_rate: number;
        tax_amount: number;
        item?: {
            id: number;
            item_name: string;
            item_type: string;
        };
    }>;
    payments: Array<{
        id: number;
        payment_date: string;
        payment_method: string;
        amount: number;
        reference: string;
    }>;
}

interface InvoicePrintProps {
    invoice: Invoice;
}

export default function InvoicePrint({ invoice }: InvoicePrintProps) {
    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    };

    const formatCurrency = (amount: number) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(amount);
    };

    const handlePrint = () => {
        window.print();
    };

    return (
        <>
            <Head title={`Invoice ${invoice.invoice_no} - Print`} />
            
            {/* Print Controls - Hidden when printing */}
            <div className="print:hidden bg-gray-100 p-4 border-b">
                <div className="flex items-center justify-between max-w-4xl mx-auto">
                    <Link href={`/invoices/${invoice.id}`}>
                        <Button variant="outline">
                            <ArrowLeft className="h-4 w-4 mr-2" />
                            Back to Invoice
                        </Button>
                    </Link>
                    <Button onClick={handlePrint}>
                        <Printer className="h-4 w-4 mr-2" />
                        Print Invoice
                    </Button>
                </div>
            </div>

            {/* Invoice Content */}
            <div className="max-w-4xl mx-auto p-8 bg-white">
                {/* Header */}
                <div className="flex justify-between items-start mb-8">
                    <div>
                        <h1 className="text-3xl font-bold text-gray-900">INVOICE</h1>
                        <div className="mt-2 text-gray-600">
                            <div>Invoice #: {invoice.invoice_no}</div>
                            <div>Date: {formatDate(invoice.date)}</div>
                            {invoice.po_number && <div>P.O. #: {invoice.po_number}</div>}
                        </div>
                    </div>
                    <div className="text-right">
                        <div className="text-lg font-semibold text-gray-900">Your Company Name</div>
                        <div className="text-gray-600">
                            <div>123 Business Street</div>
                            <div>City, State 12345</div>
                            <div>Phone: (555) 123-4567</div>
                            <div>Email: info@yourcompany.com</div>
                        </div>
                    </div>
                </div>

                {/* Customer Information */}
                <div className="grid md:grid-cols-2 gap-8 mb-8">
                    <div>
                        <h3 className="text-lg font-semibold text-gray-900 mb-2">Bill To:</h3>
                        <div className="text-gray-700">
                            <div className="font-medium">{invoice.customer.name}</div>
                            <div>{invoice.customer.email}</div>
                            <div>{invoice.customer.phone}</div>
                            {invoice.billing_address && (
                                <div className="whitespace-pre-line mt-2">{invoice.billing_address}</div>
                            )}
                        </div>
                    </div>
                    <div>
                        <h3 className="text-lg font-semibold text-gray-900 mb-2">Ship To:</h3>
                        <div className="text-gray-700">
                            {invoice.shipping_address ? (
                                <div className="whitespace-pre-line">{invoice.shipping_address}</div>
                            ) : (
                                <div className="text-gray-500 italic">Same as billing address</div>
                            )}
                        </div>
                    </div>
                </div>

                {/* Line Items Table */}
                <div className="mb-8">
                    <table className="w-full border-collapse border border-gray-300">
                        <thead>
                            <tr className="bg-gray-50">
                                <th className="border border-gray-300 px-4 py-2 text-left font-semibold">Description</th>
                                <th className="border border-gray-300 px-4 py-2 text-center font-semibold">Qty</th>
                                <th className="border border-gray-300 px-4 py-2 text-right font-semibold">Unit Price</th>
                                <th className="border border-gray-300 px-4 py-2 text-right font-semibold">Tax Rate</th>
                                <th className="border border-gray-300 px-4 py-2 text-right font-semibold">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            {invoice.line_items.map((item) => (
                                <tr key={item.id}>
                                    <td className="border border-gray-300 px-4 py-2">
                                        <div className="font-medium">{item.description}</div>
                                        {item.item && (
                                            <div className="text-sm text-gray-600">{item.item.item_type}</div>
                                        )}
                                    </td>
                                    <td className="border border-gray-300 px-4 py-2 text-center">{item.quantity}</td>
                                    <td className="border border-gray-300 px-4 py-2 text-right">{formatCurrency(item.unit_price)}</td>
                                    <td className="border border-gray-300 px-4 py-2 text-right">{item.tax_rate}%</td>
                                    <td className="border border-gray-300 px-4 py-2 text-right font-medium">{formatCurrency(item.amount)}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                {/* Totals */}
                <div className="flex justify-end mb-8">
                    <div className="w-80">
                        <div className="space-y-2">
                            <div className="flex justify-between">
                                <span>Subtotal:</span>
                                <span>{formatCurrency(invoice.subtotal)}</span>
                            </div>
                            {invoice.tax_amount > 0 && (
                                <div className="flex justify-between">
                                    <span>Tax:</span>
                                    <span>{formatCurrency(invoice.tax_amount)}</span>
                                </div>
                            )}
                            {invoice.discount_amount > 0 && (
                                <div className="flex justify-between">
                                    <span>Discount:</span>
                                    <span>-{formatCurrency(invoice.discount_amount)}</span>
                                </div>
                            )}
                            {invoice.shipping_amount > 0 && (
                                <div className="flex justify-between">
                                    <span>Shipping:</span>
                                    <span>{formatCurrency(invoice.shipping_amount)}</span>
                                </div>
                            )}
                            <div className="border-t pt-2">
                                <div className="flex justify-between text-lg font-bold">
                                    <span>Total:</span>
                                    <span>{formatCurrency(invoice.total_amount)}</span>
                                </div>
                            </div>
                            {invoice.payments_applied > 0 && (
                                <div className="flex justify-between text-green-600">
                                    <span>Payments Applied:</span>
                                    <span>{formatCurrency(invoice.payments_applied)}</span>
                                </div>
                            )}
                            <div className="border-t pt-2">
                                <div className="flex justify-between text-lg font-bold">
                                    <span>Balance Due:</span>
                                    <span className={invoice.balance_due > 0 ? 'text-red-600' : 'text-green-600'}>
                                        {formatCurrency(invoice.balance_due)}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Additional Information */}
                {(invoice.customer_message || invoice.memo || invoice.terms) && (
                    <div className="mb-8">
                        {invoice.customer_message && (
                            <div className="mb-4">
                                <h3 className="text-lg font-semibold text-gray-900 mb-2">Message:</h3>
                                <p className="text-gray-700">{invoice.customer_message}</p>
                            </div>
                        )}
                        {invoice.terms && (
                            <div className="mb-4">
                                <h3 className="text-lg font-semibold text-gray-900 mb-2">Payment Terms:</h3>
                                <p className="text-gray-700">{invoice.terms}</p>
                            </div>
                        )}
                        {invoice.memo && (
                            <div className="text-sm text-gray-500 italic">
                                <strong>Memo:</strong> {invoice.memo}
                            </div>
                        )}
                    </div>
                )}

                {/* Footer */}
                <div className="border-t pt-8 text-center text-gray-600">
                    <p>Thank you for your business!</p>
                    <p className="text-sm mt-2">
                        If you have any questions about this invoice, please contact us at info@yourcompany.com
                    </p>
                </div>
            </div>

            <style jsx>{`
                @media print {
                    .print\\:hidden {
                        display: none !important;
                    }
                    body {
                        margin: 0;
                        padding: 0;
                    }
                }
            `}</style>
        </>
    );
}
