import { useState, useEffect } from 'react';
import { Head, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { ArrowLeft, Save, Plus, Trash2, Calculator, Package, Building, Calendar, DollarSign } from 'lucide-react';
import { Link } from '@inertiajs/react';

interface Supplier {
    id: number;
    name: string;
    company_name: string;
    email: string;
}

interface Item {
    id: number;
    item_name: string;
    cost: number;
    item_type: string;
}

interface LineItem {
    id?: number;
    item_id: string;
    description: string;
    quantity: string;
    unit_price: string;
    tax_rate: string;
    unit_of_measure: string;
    notes: string;
}

interface PurchaseOrderCreateProps {
    suppliers: Supplier[];
    items: Item[];
}

export default function PurchaseOrderCreate({ suppliers, items }: PurchaseOrderCreateProps) {
    const [lineItems, setLineItems] = useState<LineItem[]>([
        { item_id: 'none', description: '', quantity: '1', unit_price: '0.00', tax_rate: '0', unit_of_measure: '', notes: '' }
    ]);

    const { data, setData, post, processing, errors } = useForm({
        supplier_id: 'none',
        order_date: new Date().toISOString().split('T')[0],
        expected_delivery_date: '',
        shipping_address: '',
        billing_address: '',
        terms: 'none',
        reference: '',
        notes: '',
        shipping_amount: '0.00',
        discount_amount: '0.00',
        created_by: '',
        line_items: lineItems,
    });

    // Update line_items when lineItems state changes
    useEffect(() => {
        setData('line_items', lineItems);
    }, [lineItems]);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        
        // Process data before submission
        const submitData = {
            ...data,
            supplier_id: data.supplier_id === 'none' ? '' : data.supplier_id,
            terms: data.terms === 'none' ? '' : data.terms,
            line_items: lineItems.map(item => ({
                ...item,
                item_id: item.item_id === 'none' ? '' : item.item_id,
                quantity: parseFloat(item.quantity) || 0,
                unit_price: parseFloat(item.unit_price) || 0,
                tax_rate: parseFloat(item.tax_rate) || 0,
            }))
        };

        post('/purchase-orders', {
            data: submitData
        });
    };

    const addLineItem = () => {
        setLineItems([...lineItems, { 
            item_id: 'none', 
            description: '', 
            quantity: '1', 
            unit_price: '0.00', 
            tax_rate: '0', 
            unit_of_measure: '', 
            notes: '' 
        }]);
    };

    const removeLineItem = (index: number) => {
        if (lineItems.length > 1) {
            setLineItems(lineItems.filter((_, i) => i !== index));
        }
    };

    const updateLineItem = (index: number, field: keyof LineItem, value: string) => {
        const updatedItems = [...lineItems];
        updatedItems[index] = { ...updatedItems[index], [field]: value };
        setLineItems(updatedItems);
    };

    const handleItemSelect = (index: number, itemId: string) => {
        if (itemId === 'none') {
            updateLineItem(index, 'item_id', 'none');
            updateLineItem(index, 'description', '');
            updateLineItem(index, 'unit_price', '0.00');
            return;
        }

        const selectedItem = items.find(item => item.id.toString() === itemId);
        if (selectedItem) {
            updateLineItem(index, 'item_id', itemId);
            updateLineItem(index, 'description', selectedItem.item_name);
            updateLineItem(index, 'unit_price', selectedItem.cost.toString());
        }
    };

    const calculateLineTotal = (item: LineItem) => {
        const quantity = parseFloat(item.quantity) || 0;
        const unitPrice = parseFloat(item.unit_price) || 0;
        const taxRate = parseFloat(item.tax_rate) || 0;
        const subtotal = quantity * unitPrice;
        const taxAmount = subtotal * (taxRate / 100);
        return subtotal + taxAmount;
    };

    const calculateTotals = () => {
        const subtotal = lineItems.reduce((sum, item) => {
            const quantity = parseFloat(item.quantity) || 0;
            const unitPrice = parseFloat(item.unit_price) || 0;
            return sum + (quantity * unitPrice);
        }, 0);

        const taxAmount = lineItems.reduce((sum, item) => {
            const quantity = parseFloat(item.quantity) || 0;
            const unitPrice = parseFloat(item.unit_price) || 0;
            const taxRate = parseFloat(item.tax_rate) || 0;
            return sum + (quantity * unitPrice * (taxRate / 100));
        }, 0);

        const shippingAmount = parseFloat(data.shipping_amount) || 0;
        const discountAmount = parseFloat(data.discount_amount) || 0;
        const total = subtotal + taxAmount + shippingAmount - discountAmount;

        return { subtotal, taxAmount, shippingAmount, discountAmount, total };
    };

    const totals = calculateTotals();

    const paymentTermsOptions = [
        'Due on Receipt',
        'Net 15',
        'Net 30',
        'Net 45',
        'Net 60',
        '2/10 Net 30',
        '1/15 Net 30',
        'COD',
        'Prepaid',
    ];

    return (
        <AuthenticatedLayout>
            <Head title="Create Purchase Order" />
            
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center gap-4">
                    <Link href="/purchase-orders">
                        <Button variant="outline" size="sm">
                            <ArrowLeft className="h-4 w-4 mr-2" />
                            Back to Purchase Orders
                        </Button>
                    </Link>
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Create Purchase Order</h1>
                        <p className="text-muted-foreground">
                            Create a new purchase order for procurement
                        </p>
                    </div>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    <div className="grid gap-6 md:grid-cols-2">
                        {/* Supplier Information */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Building className="h-5 w-5" />
                                    Supplier Information
                                </CardTitle>
                                <CardDescription>
                                    Select the supplier for this purchase order
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="space-y-2">
                                    <Label htmlFor="supplier_id">Supplier *</Label>
                                    <Select value={data.supplier_id} onValueChange={(value) => setData('supplier_id', value)}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select supplier" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="none">Select a supplier</SelectItem>
                                            {suppliers.map((supplier) => (
                                                <SelectItem key={supplier.id} value={supplier.id.toString()}>
                                                    {supplier.company_name || supplier.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.supplier_id && <p className="text-sm text-red-500">{errors.supplier_id}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="order_date">Order Date *</Label>
                                    <Input
                                        id="order_date"
                                        type="date"
                                        value={data.order_date}
                                        onChange={(e) => setData('order_date', e.target.value)}
                                        className={errors.order_date ? 'border-red-500' : ''}
                                    />
                                    {errors.order_date && <p className="text-sm text-red-500">{errors.order_date}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="expected_delivery_date">Expected Delivery Date</Label>
                                    <Input
                                        id="expected_delivery_date"
                                        type="date"
                                        value={data.expected_delivery_date}
                                        onChange={(e) => setData('expected_delivery_date', e.target.value)}
                                        className={errors.expected_delivery_date ? 'border-red-500' : ''}
                                    />
                                    {errors.expected_delivery_date && <p className="text-sm text-red-500">{errors.expected_delivery_date}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="reference">Reference</Label>
                                    <Input
                                        id="reference"
                                        value={data.reference}
                                        onChange={(e) => setData('reference', e.target.value)}
                                        placeholder="Enter reference number"
                                        className={errors.reference ? 'border-red-500' : ''}
                                    />
                                    {errors.reference && <p className="text-sm text-red-500">{errors.reference}</p>}
                                </div>
                            </CardContent>
                        </Card>

                        {/* Address Information */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Package className="h-5 w-5" />
                                    Address Information
                                </CardTitle>
                                <CardDescription>
                                    Shipping and billing addresses
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="space-y-2">
                                    <Label htmlFor="shipping_address">Shipping Address</Label>
                                    <Textarea
                                        id="shipping_address"
                                        value={data.shipping_address}
                                        onChange={(e) => setData('shipping_address', e.target.value)}
                                        placeholder="Enter shipping address"
                                        rows={3}
                                        className={errors.shipping_address ? 'border-red-500' : ''}
                                    />
                                    {errors.shipping_address && <p className="text-sm text-red-500">{errors.shipping_address}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="billing_address">Billing Address</Label>
                                    <Textarea
                                        id="billing_address"
                                        value={data.billing_address}
                                        onChange={(e) => setData('billing_address', e.target.value)}
                                        placeholder="Enter billing address"
                                        rows={3}
                                        className={errors.billing_address ? 'border-red-500' : ''}
                                    />
                                    {errors.billing_address && <p className="text-sm text-red-500">{errors.billing_address}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="terms">Payment Terms</Label>
                                    <Select value={data.terms} onValueChange={(value) => setData('terms', value)}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select payment terms" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="none">Select payment terms</SelectItem>
                                            {paymentTermsOptions.map((term) => (
                                                <SelectItem key={term} value={term}>
                                                    {term}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.terms && <p className="text-sm text-red-500">{errors.terms}</p>}
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Line Items */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Package className="h-5 w-5" />
                                Line Items
                            </CardTitle>
                            <CardDescription>
                                Add items to this purchase order
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            {lineItems.map((item, index) => (
                                <div key={index} className="grid gap-4 md:grid-cols-6 p-4 border rounded-lg">
                                    <div className="space-y-2">
                                        <Label>Item</Label>
                                        <Select value={item.item_id} onValueChange={(value) => handleItemSelect(index, value)}>
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select item" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="none">Select an item</SelectItem>
                                                {items.map((itemOption) => (
                                                    <SelectItem key={itemOption.id} value={itemOption.id.toString()}>
                                                        {itemOption.item_name}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </div>

                                    <div className="space-y-2">
                                        <Label>Description *</Label>
                                        <Input
                                            value={item.description}
                                            onChange={(e) => updateLineItem(index, 'description', e.target.value)}
                                            placeholder="Item description"
                                        />
                                    </div>

                                    <div className="space-y-2">
                                        <Label>Quantity *</Label>
                                        <Input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            value={item.quantity}
                                            onChange={(e) => updateLineItem(index, 'quantity', e.target.value)}
                                            placeholder="0"
                                        />
                                    </div>

                                    <div className="space-y-2">
                                        <Label>Unit Price *</Label>
                                        <Input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            value={item.unit_price}
                                            onChange={(e) => updateLineItem(index, 'unit_price', e.target.value)}
                                            placeholder="0.00"
                                        />
                                    </div>

                                    <div className="space-y-2">
                                        <Label>Tax Rate (%)</Label>
                                        <Input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            max="100"
                                            value={item.tax_rate}
                                            onChange={(e) => updateLineItem(index, 'tax_rate', e.target.value)}
                                            placeholder="0"
                                        />
                                    </div>

                                    <div className="space-y-2">
                                        <Label>Actions</Label>
                                        <div className="flex gap-2">
                                            <Button
                                                type="button"
                                                variant="outline"
                                                size="sm"
                                                onClick={() => removeLineItem(index)}
                                                disabled={lineItems.length === 1}
                                            >
                                                <Trash2 className="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </div>

                                    <div className="space-y-2">
                                        <Label>Unit of Measure</Label>
                                        <Input
                                            value={item.unit_of_measure}
                                            onChange={(e) => updateLineItem(index, 'unit_of_measure', e.target.value)}
                                            placeholder="e.g., pcs, kg, lbs"
                                        />
                                    </div>

                                    <div className="space-y-2">
                                        <Label>Notes</Label>
                                        <Input
                                            value={item.notes}
                                            onChange={(e) => updateLineItem(index, 'notes', e.target.value)}
                                            placeholder="Additional notes"
                                        />
                                    </div>

                                    <div className="space-y-2">
                                        <Label>Total</Label>
                                        <div className="text-sm font-medium">
                                            ${calculateLineTotal(item).toFixed(2)}
                                        </div>
                                    </div>
                                </div>
                            ))}

                            <Button type="button" variant="outline" onClick={addLineItem}>
                                <Plus className="h-4 w-4 mr-2" />
                                Add Line Item
                            </Button>
                        </CardContent>
                    </Card>

                    {/* Additional Information */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <DollarSign className="h-5 w-5" />
                                Additional Information
                            </CardTitle>
                            <CardDescription>
                                Shipping, discounts, and other details
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="shipping_amount">Shipping Amount</Label>
                                    <Input
                                        id="shipping_amount"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        value={data.shipping_amount}
                                        onChange={(e) => setData('shipping_amount', e.target.value)}
                                        placeholder="0.00"
                                        className={errors.shipping_amount ? 'border-red-500' : ''}
                                    />
                                    {errors.shipping_amount && <p className="text-sm text-red-500">{errors.shipping_amount}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="discount_amount">Discount Amount</Label>
                                    <Input
                                        id="discount_amount"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        value={data.discount_amount}
                                        onChange={(e) => setData('discount_amount', e.target.value)}
                                        placeholder="0.00"
                                        className={errors.discount_amount ? 'border-red-500' : ''}
                                    />
                                    {errors.discount_amount && <p className="text-sm text-red-500">{errors.discount_amount}</p>}
                                </div>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="created_by">Created By</Label>
                                <Input
                                    id="created_by"
                                    value={data.created_by}
                                    onChange={(e) => setData('created_by', e.target.value)}
                                    placeholder="Enter creator name"
                                    className={errors.created_by ? 'border-red-500' : ''}
                                />
                                {errors.created_by && <p className="text-sm text-red-500">{errors.created_by}</p>}
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="notes">Notes</Label>
                                <Textarea
                                    id="notes"
                                    value={data.notes}
                                    onChange={(e) => setData('notes', e.target.value)}
                                    placeholder="Enter any additional notes"
                                    rows={3}
                                    className={errors.notes ? 'border-red-500' : ''}
                                />
                                {errors.notes && <p className="text-sm text-red-500">{errors.notes}</p>}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Totals Summary */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Calculator className="h-5 w-5" />
                                Order Summary
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-2">
                                <div className="flex justify-between">
                                    <span>Subtotal:</span>
                                    <span>${totals.subtotal.toFixed(2)}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span>Tax Amount:</span>
                                    <span>${totals.taxAmount.toFixed(2)}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span>Shipping:</span>
                                    <span>${totals.shippingAmount.toFixed(2)}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span>Discount:</span>
                                    <span>-${totals.discountAmount.toFixed(2)}</span>
                                </div>
                                <div className="flex justify-between font-bold text-lg border-t pt-2">
                                    <span>Total:</span>
                                    <span>${totals.total.toFixed(2)}</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Form Actions */}
                    <div className="flex justify-end gap-4">
                        <Link href="/purchase-orders">
                            <Button type="button" variant="outline">
                                Cancel
                            </Button>
                        </Link>
                        <Button type="submit" disabled={processing}>
                            <Save className="h-4 w-4 mr-2" />
                            {processing ? 'Creating...' : 'Create Purchase Order'}
                        </Button>
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}




