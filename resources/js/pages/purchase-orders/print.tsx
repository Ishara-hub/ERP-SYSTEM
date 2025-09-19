import { Head } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { ArrowLeft, Printer } from 'lucide-react';
import { Link } from '@inertiajs/react';

interface PurchaseOrder {
    id: number;
    po_number: string;
    order_date: string;
    expected_delivery_date: string;
    actual_delivery_date: string;
    status: string;
    subtotal: number;
    tax_amount: number;
    shipping_amount: number;
    discount_amount: number;
    total_amount: number;
    shipping_address: string;
    billing_address: string;
    terms: string;
    reference: string;
    notes: string;
    created_by: string;
    approved_by: string;
    approved_at: string;
    created_at: string;
    supplier: {
        id: number;
        name: string;
        company_name: string;
        email: string;
        phone: string;
        address: string;
        contact_person: string;
    };
    items: {
        id: number;
        description: string;
        quantity: number;
        unit_price: number;
        amount: number;
        received_quantity: number;
        tax_rate: number;
        tax_amount: number;
        unit_of_measure: string;
        notes: string;
        item: {
            id: number;
            item_name: string;
        } | null;
    }[];
}

interface PurchaseOrderPrintProps {
    purchaseOrder: PurchaseOrder;
}

export default function PurchaseOrderPrint({ purchaseOrder }: PurchaseOrderPrintProps) {
    const formatCurrency = (amount: number) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(amount);
    };

    const formatDate = (dateString: string) => {
        if (!dateString) return '';
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    };

    const handlePrint = () => {
        window.print();
    };

    return (
        <div className="min-h-screen bg-white">
            <Head title={`Purchase Order - ${purchaseOrder.po_number}`} />
            
            {/* Print Controls - Hidden when printing */}
            <div className="print:hidden bg-gray-50 p-4 border-b">
                <div className="flex items-center justify-between max-w-4xl mx-auto">
                    <Link href={`/purchase-orders/${purchaseOrder.id}`}>
                        <Button variant="outline">
                            <ArrowLeft className="h-4 w-4 mr-2" />
                            Back to Purchase Order
                        </Button>
                    </Link>
                    <Button onClick={handlePrint}>
                        <Printer className="h-4 w-4 mr-2" />
                        Print Purchase Order
                    </Button>
                </div>
            </div>

            {/* Purchase Order Content */}
            <div className="max-w-4xl mx-auto p-8 print:p-0 print:max-w-none">
                {/* Header */}
                <div className="flex justify-between items-start mb-8 print:mb-6">
                    <div>
                        <h1 className="text-3xl font-bold text-gray-900 print:text-2xl">PURCHASE ORDER</h1>
                        <p className="text-lg text-gray-600 print:text-base">PO Number: {purchaseOrder.po_number}</p>
                        {purchaseOrder.reference && (
                            <p className="text-sm text-gray-500">Reference: {purchaseOrder.reference}</p>
                        )}
                    </div>
                    <div className="text-right">
                        <div className="text-sm text-gray-600">
                            <p><strong>Order Date:</strong> {formatDate(purchaseOrder.order_date)}</p>
                            {purchaseOrder.expected_delivery_date && (
                                <p><strong>Expected Delivery:</strong> {formatDate(purchaseOrder.expected_delivery_date)}</p>
                            )}
                            {purchaseOrder.created_by && (
                                <p><strong>Created By:</strong> {purchaseOrder.created_by}</p>
                            )}
                        </div>
                    </div>
                </div>

                {/* Supplier Information */}
                <div className="grid grid-cols-2 gap-8 mb-8 print:mb-6">
                    <div>
                        <h3 className="text-lg font-semibold text-gray-900 mb-3 print:text-base">Vendor Information</h3>
                        <div className="text-sm text-gray-700">
                            <p className="font-semibold">{purchaseOrder.supplier.company_name || purchaseOrder.supplier.name}</p>
                            {purchaseOrder.supplier.contact_person && (
                                <p>Contact: {purchaseOrder.supplier.contact_person}</p>
                            )}
                            <p>{purchaseOrder.supplier.address}</p>
                            <p>Email: {purchaseOrder.supplier.email}</p>
                            <p>Phone: {purchaseOrder.supplier.phone}</p>
                        </div>
                    </div>
                    <div>
                        <h3 className="text-lg font-semibold text-gray-900 mb-3 print:text-base">Shipping Address</h3>
                        <div className="text-sm text-gray-700">
                            {purchaseOrder.shipping_address ? (
                                <div className="whitespace-pre-line">{purchaseOrder.shipping_address}</div>
                            ) : (
                                <div className="whitespace-pre-line">{purchaseOrder.supplier.address}</div>
                            )}
                        </div>
                    </div>
                </div>

                {/* Billing Address */}
                {purchaseOrder.billing_address && (
                    <div className="mb-8 print:mb-6">
                        <h3 className="text-lg font-semibold text-gray-900 mb-3 print:text-base">Billing Address</h3>
                        <div className="text-sm text-gray-700 whitespace-pre-line">
                            {purchaseOrder.billing_address}
                        </div>
                    </div>
                )}

                {/* Terms and Conditions */}
                {(purchaseOrder.terms || purchaseOrder.notes) && (
                    <div className="mb-8 print:mb-6">
                        <div className="grid grid-cols-2 gap-8">
                            {purchaseOrder.terms && (
                                <div>
                                    <h3 className="text-lg font-semibold text-gray-900 mb-3 print:text-base">Payment Terms</h3>
                                    <p className="text-sm text-gray-700">{purchaseOrder.terms}</p>
                                </div>
                            )}
                            {purchaseOrder.notes && (
                                <div>
                                    <h3 className="text-lg font-semibold text-gray-900 mb-3 print:text-base">Notes</h3>
                                    <p className="text-sm text-gray-700 whitespace-pre-line">{purchaseOrder.notes}</p>
                                </div>
                            )}
                        </div>
                    </div>
                )}

                {/* Line Items Table */}
                <div className="mb-8 print:mb-6">
                    <h3 className="text-lg font-semibold text-gray-900 mb-4 print:text-base">Items Ordered</h3>
                    <div className="overflow-x-auto">
                        <table className="w-full border-collapse border border-gray-300">
                            <thead>
                                <tr className="bg-gray-50">
                                    <th className="border border-gray-300 px-4 py-2 text-left text-sm font-semibold text-gray-900">Description</th>
                                    <th className="border border-gray-300 px-4 py-2 text-center text-sm font-semibold text-gray-900">Qty</th>
                                    <th className="border border-gray-300 px-4 py-2 text-center text-sm font-semibold text-gray-900">Unit</th>
                                    <th className="border border-gray-300 px-4 py-2 text-right text-sm font-semibold text-gray-900">Unit Price</th>
                                    <th className="border border-gray-300 px-4 py-2 text-right text-sm font-semibold text-gray-900">Tax Rate</th>
                                    <th className="border border-gray-300 px-4 py-2 text-right text-sm font-semibold text-gray-900">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                {purchaseOrder.items.map((item, index) => (
                                    <tr key={item.id}>
                                        <td className="border border-gray-300 px-4 py-2 text-sm text-gray-700">
                                            <div>
                                                <div className="font-medium">{item.description}</div>
                                                {item.item && (
                                                    <div className="text-xs text-gray-500">Item: {item.item.item_name}</div>
                                                )}
                                                {item.notes && (
                                                    <div className="text-xs text-gray-500 mt-1">{item.notes}</div>
                                                )}
                                            </div>
                                        </td>
                                        <td className="border border-gray-300 px-4 py-2 text-center text-sm text-gray-700">
                                            {item.quantity}
                                        </td>
                                        <td className="border border-gray-300 px-4 py-2 text-center text-sm text-gray-700">
                                            {item.unit_of_measure || 'pcs'}
                                        </td>
                                        <td className="border border-gray-300 px-4 py-2 text-right text-sm text-gray-700">
                                            {formatCurrency(item.unit_price)}
                                        </td>
                                        <td className="border border-gray-300 px-4 py-2 text-right text-sm text-gray-700">
                                            {item.tax_rate}%
                                        </td>
                                        <td className="border border-gray-300 px-4 py-2 text-right text-sm text-gray-700">
                                            <div>{formatCurrency(item.amount)}</div>
                                            {item.tax_amount > 0 && (
                                                <div className="text-xs text-gray-500">
                                                    +{formatCurrency(item.tax_amount)} tax
                                                </div>
                                            )}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>

                {/* Totals */}
                <div className="flex justify-end mb-8 print:mb-6">
                    <div className="w-80">
                        <div className="space-y-2">
                            <div className="flex justify-between text-sm">
                                <span>Subtotal:</span>
                                <span>{formatCurrency(purchaseOrder.subtotal)}</span>
                            </div>
                            {purchaseOrder.tax_amount > 0 && (
                                <div className="flex justify-between text-sm">
                                    <span>Tax Amount:</span>
                                    <span>{formatCurrency(purchaseOrder.tax_amount)}</span>
                                </div>
                            )}
                            {purchaseOrder.shipping_amount > 0 && (
                                <div className="flex justify-between text-sm">
                                    <span>Shipping:</span>
                                    <span>{formatCurrency(purchaseOrder.shipping_amount)}</span>
                                </div>
                            )}
                            {purchaseOrder.discount_amount > 0 && (
                                <div className="flex justify-between text-sm">
                                    <span>Discount:</span>
                                    <span>-{formatCurrency(purchaseOrder.discount_amount)}</span>
                                </div>
                            )}
                            <div className="border-t border-gray-300 pt-2">
                                <div className="flex justify-between font-bold text-lg">
                                    <span>Total:</span>
                                    <span>{formatCurrency(purchaseOrder.total_amount)}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Footer */}
                <div className="mt-12 print:mt-8">
                    <div className="grid grid-cols-2 gap-8">
                        <div>
                            <h3 className="text-lg font-semibold text-gray-900 mb-3 print:text-base">Authorized Signature</h3>
                            <div className="border-b border-gray-300 w-48 h-16"></div>
                            <p className="text-sm text-gray-600 mt-2">Signature</p>
                        </div>
                        <div>
                            <h3 className="text-lg font-semibold text-gray-900 mb-3 print:text-base">Date</h3>
                            <div className="border-b border-gray-300 w-48 h-16"></div>
                            <p className="text-sm text-gray-600 mt-2">Date</p>
                        </div>
                    </div>
                </div>

                {/* Status Information */}
                <div className="mt-8 print:mt-6 text-center text-sm text-gray-500">
                    <p>Status: {purchaseOrder.status.charAt(0).toUpperCase() + purchaseOrder.status.slice(1)}</p>
                    {purchaseOrder.approved_by && (
                        <p>Approved by: {purchaseOrder.approved_by}</p>
                    )}
                    {purchaseOrder.approved_at && (
                        <p>Approved on: {formatDate(purchaseOrder.approved_at)}</p>
                    )}
                </div>
            </div>
        </div>
    );
}





