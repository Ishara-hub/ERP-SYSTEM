# POS Dashboard Guide

## Overview
The POS (Point of Sale) Dashboard is a comprehensive invoice creation system designed for retail and wholesale stores. It provides customer-specific pricing, invoice history, and streamlined invoice creation.

## Features

### 1. Customer Selection
- **Search Functionality**: Type to search customers by name or company
- **Customer Information Display**: Shows selected customer details
- **Customer History**: Displays recent invoices for the selected customer

### 2. Item Selection
- **Search Functionality**: Type to search items by name or description
- **Item Information Display**: Shows selected item details including unit of measure

### 3. Customer-Specific Pricing
- **Current Price**: Shows the standard selling price of the item
- **Last Price**: Displays the last price this customer paid for the item
- **Invoice History**: Shows which invoice the last price came from
- **Smart Defaults**: Automatically uses the last customer price when available

### 4. Invoice Creation
- **Cart System**: Add multiple items with different quantities and prices
- **Real-time Totals**: Live calculation of invoice totals
- **Validation**: Ensures valid quantities and prices before adding to cart
- **Invoice Generation**: Creates properly formatted invoices with line items

### 5. Invoice History Display
- **Recent Invoices**: Shows the last 10 invoices for the selected customer
- **Invoice Details**: Displays invoice number, date, total amount, and status
- **Item Summary**: Shows items in each invoice with quantities
- **Status Indicators**: Color-coded status (paid, partial, unpaid)

## Usage

### Accessing the POS Dashboard
1. Navigate to `/pos` in your browser
2. Or click "POS Dashboard" in the sidebar navigation

### Creating an Invoice
1. **Select Customer**: Type in the customer search box to find and select a customer
2. **Select Item**: Type in the item search box to find and select an item
3. **Review Pricing**: Check the current price and last customer price
4. **Adjust Price**: Modify the unit price if needed
5. **Set Quantity**: Enter the desired quantity
6. **Add to Cart**: Click "Add to Cart" to add the item
7. **Repeat**: Add more items as needed
8. **Create Invoice**: Click "Create Invoice" when ready

### Key Features Explained

#### Customer-Specific Pricing
The system remembers the last price each customer paid for each item. This is useful for:
- Wholesale customers who get different pricing
- Loyal customers with special rates
- Negotiated pricing agreements

#### Invoice History
The right panel shows recent invoices for the selected customer, helping you:
- See what they've purchased before
- Check their payment history
- Reference previous orders

#### Cart Management
- Add multiple items with different quantities
- Adjust prices per item
- Remove items from cart
- See running total

## Technical Details

### Database Structure
- **Invoices Table**: Main invoice records
- **Invoice Line Items Table**: Individual items in each invoice
- **Customers Table**: Customer information
- **Items Table**: Product/service catalog

### API Endpoints
- `GET /pos` - POS Dashboard page
- `POST /pos/customer-pricing` - Get customer-specific pricing
- `POST /pos/customer-invoices` - Get customer invoice history
- `POST /pos/create-invoice` - Create new invoice

### File Structure
```
app/Http/Controllers/POS/
├── POSController.php          # Main POS controller

resources/views/pos/
├── dashboard.blade.php        # Main POS dashboard view
└── invoice-mini-card.blade.php # Invoice card component

routes/
└── web.php                   # POS routes
```

## Testing

Run the test script to verify functionality:
```bash
php test_pos.php
```

This will test:
- Database connectivity
- Customer and item data
- Customer-specific pricing logic
- Invoice creation process

## Styling

The POS dashboard uses:
- **Tailwind CSS** for styling
- **Responsive Design** for mobile and desktop
- **Modern UI Components** with hover effects and transitions
- **Color-coded Status** indicators
- **Clean Typography** for readability

## Security

- All routes are protected by authentication middleware
- CSRF protection on all forms
- Input validation on all user inputs
- SQL injection protection through Eloquent ORM

## Browser Compatibility

- Modern browsers (Chrome, Firefox, Safari, Edge)
- Mobile responsive design
- JavaScript required for full functionality

## Troubleshooting

### Common Issues

1. **Customer not found**: Ensure customer is active in the database
2. **Item not found**: Check if item is active and has a sales price
3. **Pricing not loading**: Verify customer and item IDs are valid
4. **Invoice creation fails**: Check for validation errors in the form

### Debug Mode
Enable Laravel debug mode in `.env`:
```
APP_DEBUG=true
```

## Future Enhancements

- **Barcode Scanning**: Support for barcode scanners
- **Payment Processing**: Integrated payment processing
- **Receipt Printing**: Direct receipt printing
- **Inventory Management**: Real-time inventory updates
- **Discount System**: Customer and item-level discounts
- **Tax Calculation**: Automatic tax calculations
- **Multi-location Support**: Support for multiple store locations

## Support

For technical support or feature requests, please contact the development team or create an issue in the project repository.
