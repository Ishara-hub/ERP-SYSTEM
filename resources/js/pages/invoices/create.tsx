import { useState, useEffect } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Badge } from '@/components/ui/badge';
import { 
    ArrowLeft, 
    Save, 
    X, 
    Plus, 
    Trash2,
    FileText,
    User,
    Calendar,
    DollarSign,
    Package
} from 'lucide-react';

interface Customer {
    id: number;
    name: string;
    email: string;
    address: string;
}

interface Item {
    id: number;
    item_name: string;
    sales_price: number;
    item_type: string;
}

interface LineItem {
    id?: number;
    item_id: string;
    description: string;
    quantity: number;
    unit_price: number;
    amount: number;
    tax_rate: number;
    tax_amount: number;
}

interface CreateInvoiceProps {
    customers: Customer[];
    items: Item[];
}

export default function CreateInvoice({ customers, items }: CreateInvoiceProps) {
    const { data, setData, post, processing, errors } = useForm({
        customer_id: 'none',
        date: new Date().toISOString().split('T')[0],
        ship_date: new Date().toISOString().split('T')[0],
        po_number: '',
        terms: 'none',
        rep: '',
        via: '',
        fob: '',
        customer_message: '',
        memo: '',
        billing_address: '',
        shipping_address: '',
        template: 'default',
        is_online_payment_enabled: false,
        line_items: [
            {
                item_id: 'none',
                description: '',
                quantity: 1,
                unit_price: 0,
                amount: 0,
                tax_rate: 0,
                tax_amount: 0,
            }
        ] as LineItem[],
    });

    const [selectedCustomer, setSelectedCustomer] = useState<Customer | null>(null);
    const [subtotal, setSubtotal] = useState(0);
    const [taxAmount, setTaxAmount] = useState(0);
    const [totalAmount, setTotalAmount] = useState(0);

    // Calculate totals when line items change
    useEffect(() => {
        const subtotal = data.line_items.reduce((sum, item) => sum + item.amount, 0);
        const taxAmount = data.line_items.reduce((sum, item) => sum + item.tax_amount, 0);
        const total = subtotal + taxAmount;

        setSubtotal(subtotal);
        setTaxAmount(taxAmount);
        setTotalAmount(total);
    }, [data.line_items]);

    // Update customer addresses when customer changes
    useEffect(() => {
        if (selectedCustomer) {
            setData('billing_address', selectedCustomer.address);
            setData('shipping_address', selectedCustomer.address);
        }
    }, [selectedCustomer]);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        
        // Process form data to convert "none" values to empty strings
        const processedData = {
            ...data,
            customer_id: data.customer_id === 'none' ? '' : data.customer_id,
            terms: data.terms === 'none' ? '' : data.terms,
            line_items: data.line_items.map(item => ({
                ...item,
                item_id: item.item_id === 'none' ? '' : item.item_id,
            }))
        };
        
        // Update form data with processed values
        Object.keys(processedData).forEach(key => {
            setData(key as keyof typeof data, processedData[key as keyof typeof processedData]);
        });
        
        post('/invoices');
    };

    const addLineItem = () => {
        setData('line_items', [
            ...data.line_items,
            {
                item_id: 'none',
                description: '',
                quantity: 1,
                unit_price: 0,
                amount: 0,
                tax_rate: 0,
                tax_amount: 0,
            }
        ]);
    };

    const removeLineItem = (index: number) => {
        if (data.line_items.length > 1) {
            const newLineItems = data.line_items.filter((_, i) => i !== index);
            setData('line_items', newLineItems);
        }
    };

    const updateLineItem = (index: number, field: keyof LineItem, value: any) => {
        const newLineItems = [...data.line_items];
        newLineItems[index] = { ...newLineItems[index], [field]: value };

        // Calculate amount and tax
        if (field === 'quantity' || field === 'unit_price') {
            const quantity = field === 'quantity' ? value : newLineItems[index].quantity;
            const unitPrice = field === 'unit_price' ? value : newLineItems[index].unit_price;
            const amount = quantity * unitPrice;
            const taxAmount = amount * (newLineItems[index].tax_rate / 100);
            
            newLineItems[index].amount = amount;
            newLineItems[index].tax_amount = taxAmount;
        } else if (field === 'tax_rate') {
            const taxAmount = newLineItems[index].amount * (value / 100);
            newLineItems[index].tax_amount = taxAmount;
        }

        setData('line_items', newLineItems);
    };

    const handleItemSelect = (index: number, itemId: string) => {
        if (itemId === 'none') {
            updateLineItem(index, 'item_id', '');
            updateLineItem(index, 'description', '');
            updateLineItem(index, 'unit_price', 0);
        } else {
            const item = items.find(i => i.id.toString() === itemId);
            if (item) {
                updateLineItem(index, 'item_id', itemId);
                updateLineItem(index, 'description', item.item_name);
                updateLineItem(index, 'unit_price', item.sales_price);
            }
        }
    };

    const formatCurrency = (amount: number) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(amount);
    };

    return (
        <AuthenticatedLayout>
            <Head title="Create Invoice" />
            
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Link href="/invoices">
                            <Button variant="outline" size="sm">
                                <ArrowLeft className="h-4 w-4 mr-2" />
                                Back to Invoices
                            </Button>
                        </Link>
                        <div>
                            <h1 className="text-3xl font-bold tracking-tight">Create Invoice</h1>
                            <p className="text-muted-foreground">
                                Create a new invoice for your customer
                            </p>
                        </div>
                    </div>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    {/* Invoice Header */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <FileText className="h-5 w-5" />
                                Invoice Details
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-4 md:grid-cols-3">
                                <div className="space-y-2">
                                    <Label htmlFor="customer_id">Customer *</Label>
                                    <Select 
                                        value={data.customer_id} 
                                        onValueChange={(value) => {
                                            if (value === 'none') {
                                                setData('customer_id', '');
                                                setSelectedCustomer(null);
                                            } else {
                                                setData('customer_id', value);
                                                const customer = customers.find(c => c.id.toString() === value);
                                                setSelectedCustomer(customer || null);
                                            }
                                        }}
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select customer" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="none">Select a customer</SelectItem>
                                            {customers.map((customer) => (
                                                <SelectItem key={customer.id} value={customer.id.toString()}>
                                                    {customer.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.customer_id && (
                                        <p className="text-sm text-red-600">{errors.customer_id}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="date">Date *</Label>
                                    <div className="relative">
                                        <Calendar className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                                        <Input
                                            id="date"
                                            type="date"
                                            value={data.date}
                                            onChange={(e) => setData('date', e.target.value)}
                                            className="pl-10"
                                        />
                                    </div>
                                    {errors.date && (
                                        <p className="text-sm text-red-600">{errors.date}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="po_number">P.O. Number</Label>
                                    <Input
                                        id="po_number"
                                        value={data.po_number}
                                        onChange={(e) => setData('po_number', e.target.value)}
                                        placeholder="Enter P.O. number"
                                    />
                                </div>
                            </div>

                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="terms">Terms</Label>
                                    <Select value={data.terms} onValueChange={(value) => setData('terms', value)}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select terms" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="none">Select terms</SelectItem>
                                            <SelectItem value="net_15">Net 15</SelectItem>
                                            <SelectItem value="net_30">Net 30</SelectItem>
                                            <SelectItem value="net_60">Net 60</SelectItem>
                                            <SelectItem value="due_on_receipt">Due on Receipt</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="ship_date">Ship Date</Label>
                                    <div className="relative">
                                        <Calendar className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                                        <Input
                                            id="ship_date"
                                            type="date"
                                            value={data.ship_date}
                                            onChange={(e) => setData('ship_date', e.target.value)}
                                            className="pl-10"
                                        />
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Billing & Shipping Addresses */}
                    <div className="grid gap-6 md:grid-cols-2">
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <User className="h-5 w-5" />
                                    Bill To
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <Textarea
                                    value={data.billing_address}
                                    onChange={(e) => setData('billing_address', e.target.value)}
                                    placeholder="Enter billing address"
                                    rows={4}
                                />
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Package className="h-5 w-5" />
                                    Ship To
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <Textarea
                                    value={data.shipping_address}
                                    onChange={(e) => setData('shipping_address', e.target.value)}
                                    placeholder="Enter shipping address"
                                    rows={4}
                                />
                            </CardContent>
                        </Card>
                    </div>

                    {/* Line Items */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Line Items</CardTitle>
                            <CardDescription>
                                Add products or services to this invoice
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {data.line_items.map((item, index) => (
                                    <div key={index} className="grid gap-4 md:grid-cols-6 items-end p-4 border rounded-lg">
                                        <div className="space-y-2">
                                            <Label>Item</Label>
                                            <Select 
                                                value={item.item_id} 
                                                onValueChange={(value) => handleItemSelect(index, value)}
                                            >
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
                                            <Label>Description</Label>
                                            <Input
                                                value={item.description}
                                                onChange={(e) => updateLineItem(index, 'description', e.target.value)}
                                                placeholder="Enter description"
                                            />
                                        </div>

                                        <div className="space-y-2">
                                            <Label>Quantity</Label>
                                            <Input
                                                type="number"
                                                step="0.01"
                                                min="0.01"
                                                value={item.quantity}
                                                onChange={(e) => updateLineItem(index, 'quantity', parseFloat(e.target.value) || 0)}
                                            />
                                        </div>

                                        <div className="space-y-2">
                                            <Label>Unit Price</Label>
                                            <Input
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                value={item.unit_price}
                                                onChange={(e) => updateLineItem(index, 'unit_price', parseFloat(e.target.value) || 0)}
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
                                                onChange={(e) => updateLineItem(index, 'tax_rate', parseFloat(e.target.value) || 0)}
                                            />
                                        </div>

                                        <div className="flex items-end gap-2">
                                            <div className="flex-1">
                                                <Label>Amount</Label>
                                                <div className="p-2 bg-muted rounded text-sm font-medium">
                                                    {formatCurrency(item.amount)}
                                                </div>
                                            </div>
                                            {data.line_items.length > 1 && (
                                                <Button
                                                    type="button"
                                                    variant="outline"
                                                    size="sm"
                                                    onClick={() => removeLineItem(index)}
                                                >
                                                    <Trash2 className="h-4 w-4" />
                                                </Button>
                                            )}
                                        </div>
                                    </div>
                                ))}

                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={addLineItem}
                                    className="w-full"
                                >
                                    <Plus className="h-4 w-4 mr-2" />
                                    Add Line Item
                                </Button>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Invoice Totals */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Invoice Summary</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                <div className="flex justify-between">
                                    <span>Subtotal:</span>
                                    <span className="font-medium">{formatCurrency(subtotal)}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span>Tax:</span>
                                    <span className="font-medium">{formatCurrency(taxAmount)}</span>
                                </div>
                                <div className="border-t pt-4">
                                    <div className="flex justify-between text-lg font-bold">
                                        <span>Total:</span>
                                        <span>{formatCurrency(totalAmount)}</span>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Additional Information */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Additional Information</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="customer_message">Customer Message</Label>
                                    <Select value={data.customer_message} onValueChange={(value) => setData('customer_message', value)}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select message" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="thank_you">Thank you for your business!</SelectItem>
                                            <SelectItem value="payment_terms">Please remit payment within the terms specified.</SelectItem>
                                            <SelectItem value="custom">Custom message</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="template">Template</Label>
                                    <Select value={data.template} onValueChange={(value) => setData('template', value)}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select template" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="default">Default Template</SelectItem>
                                            <SelectItem value="professional">Professional</SelectItem>
                                            <SelectItem value="simple">Simple</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="memo">Memo</Label>
                                <Textarea
                                    id="memo"
                                    value={data.memo}
                                    onChange={(e) => setData('memo', e.target.value)}
                                    placeholder="Enter internal memo"
                                    rows={3}
                                />
                            </div>

                            <div className="flex items-center space-x-2">
                                <Switch
                                    id="is_online_payment_enabled"
                                    checked={data.is_online_payment_enabled}
                                    onCheckedChange={(checked) => setData('is_online_payment_enabled', checked)}
                                />
                                <Label htmlFor="is_online_payment_enabled">Enable online payment</Label>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Action Buttons */}
                    <div className="flex items-center justify-end gap-4">
                        <Link href="/invoices">
                            <Button variant="outline">
                                <X className="h-4 w-4 mr-2" />
                                Cancel
                            </Button>
                        </Link>
                        <Button type="submit" disabled={processing}>
                            <Save className="h-4 w-4 mr-2" />
                            {processing ? 'Creating...' : 'Create Invoice'}
                        </Button>
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
